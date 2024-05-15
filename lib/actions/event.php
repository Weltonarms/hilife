<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/lib/auth/session.php');
include($_SERVER['DOCUMENT_ROOT'] . '/lib/common.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/lib/validations/event.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/config/event.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/lib/email.php');
include ($_SERVER['DOCUMENT_ROOT'] . '/lib/notify.php');
include ($_SERVER['DOCUMENT_ROOT'].'/lib/event.php');

$event_timings = array();

if (isset($_POST['dateInput']) && count($_POST['dateInput']) > 0) {
  $event_timings['date'] = $utils->normaliseDate($_POST['dateInput']);
}

if (isset($_POST['startTimeInput'])) {
  $start_parts = array_filter($_POST['startTimeInput'], function($v) {
    return strlen($v) > 0;
  }, ARRAY_FILTER_USE_BOTH);

  if (count($start_parts) > 0) {
    $event_timings['start_time'] = $utils->normaliseTime($start_parts);
  }
}

if (isset($_POST['finishTimeInput'])) {
  $finish_parts = array_filter($_POST['finishTimeInput'], function($v) {
    return strlen($v) > 0;
  }, ARRAY_FILTER_USE_BOTH);

  if (count($finish_parts) > 0) {
    $event_timings['finish_time'] = $utils->normaliseTime($finish_parts);
  }
}

if (isset($_POST['setupTimeInput'])) {
  $setup_parts = array_filter($_POST['setupTimeInput'], function($v) {
    return strlen($v) > 0;
  }, ARRAY_FILTER_USE_BOTH);

  if (count($setup_parts) > 0) {
    $event_timings['setup_time'] = $utils->normaliseTime($setup_parts);
  }
}

if ($user->isAdmin() || (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']))) {
 
  // Verify the reCAPTCHA API response 
  $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . constant("GOOGLE_RECAPTCHA") . '&response=' . $_POST['g-recaptcha-response']); 
   
  // Decode JSON data of API response 
  $responseData = json_decode($verifyResponse);
   
  // If the reCAPTCHA API response is valid 
  if($responseData->success || $user->isAdmin()) {

    if ($_POST['action'] == 'create') {

      $invalid = eventInvalid(array('primary_contact' => $_POST['event']['primary_contact'], 'email' => $_POST['event']['email'], 'date' => $event_timings['date'], 'status' => $_POST['admin']['status']));
      $spam = eventIsSpam($_POST['event']);
      if ($invalid) {
        Notify::add('error', 'Event cannot be created - ' . $invalid);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
      } elseif ($spam) {
        Notify::add('error', 'Event cannot be created - ' . $spam);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
      } else {
        $database->connection->beginTransaction();
        $query = $database->prepare("INSERT INTO events (type, email, location, venue_name, venue_address, client_address, client_telephone, primary_contact, secondary_contact, date, numbers, start_time, finish_time, setup_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute(array($_POST['event']["type"], $_POST['event']["email"], $_POST['event']["location"], $_POST['event']["venue_name"], $_POST['event']["venue_address"], $_POST['event']["client_address"], $_POST['event']["client_telephone"], $_POST['event']["primary_contact"], $_POST['event']["secondary_contact"], $event_timings['date'], $_POST['event']["numbers"], $event_timings['start_time'], $event_timings['finish_time'], $event_timings['setup_time']));

        $event_id = $database->connection->lastInsertId();
        $database->connection->commit();

        if (count($_POST['admin']) > 0) {

          $insertFields = '';
          $parameters = '';

          foreach ($_POST['admin'] as $key => $value) {
            $insertFields .= $key . ", ";
            $parameters .= ":" . $key . ", ";
          }

          $sql = "INSERT INTO events_admin (event_id, " . rtrim($insertFields, ", ") . ") VALUES (:event_id, " . rtrim($parameters, ", ") . ")";
          $query = $database->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
          $query->execute(array_merge(array(':event_id' => $event_id), $_POST['admin']));
        }

        $event_updated = EventFactory::create(array(
          'events.id' => $event_id
        ));

        $event_updated->date = $utils->prettyDateFormat($event_updated->date);

        Email::send('admin', $event_updated);
        Email::send('customer', $event_updated);

        $config = $event_config[$user->isAdmin() ? 'admin' : 'customer'][$event_updated->status];
        if (isset($config['notification'])) {
          Notify::add($config['notification']['type'], $utils->templateString($config['notification']['text'], $event_updated));
        }

        $redirect = $user->isAdmin() ? '/admin/events': '/';

        header('Location: ' . $redirect);
      }
    }
  } else{
    Notify::add('error', 'Invalid Recaptcha form submission');
    header('Location: /');
  }
} else {
  header("Location:".$_SERVER['HTTP_REFERER']);
}

if ($_POST['action'] == 'update') {
  if (isset($_POST['event']) && count($_POST['event']) > 0) {

    $updateFields = '';

    foreach ($_POST['event'] as $key => $value) {
      $updateFields .= $key . "=:" . $key . ", ";
    }

    foreach ($event_timings as $key => $value) {
      $updateFields .= $key . "=:" . $key . ", ";
    }
    
    $sql = "UPDATE events SET " . rtrim($updateFields, ", ") . " WHERE id = :event_id";
    $query = $database->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $query->execute(array_merge(array(':event_id' => $_POST['id']), $_POST['event'], $event_timings));
  }

  if (isset($_POST['admin']) && count($_POST['admin']) > 0) {

    $updateFields = '';

    foreach ($_POST['admin'] as $key => $value) {
      $updateFields .= $key . "=:" . $key . ", ";
    }

    $sql = "UPDATE events_admin SET " . rtrim($updateFields, ", ") . " WHERE event_id = :event_id";
    $query = $database->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $query->execute(array_merge(array(':event_id' => $_POST['id']), $_POST['admin']));
  }

  $event_updated = EventFactory::create(array(
    'events.id' => $_POST['id']
  ), true);

  $query = "SELECT status FROM events_admin WHERE event_id=\"" . $_POST['id']  . "\"";
  $event_orig_status = $database->query($query)->fetchColumn();

  if ($event_updated->status != $event_orig_status) {

    $event_updated->date = $utils->prettyDateFormat($event_updated->date);

    Email::send('admin', $event_updated);
    Email::send('customer', $event_updated);

    if ($event_updated->status === 'confirmed') {
      Email::send('dj', $event_updated);
    }
  }

  Notify::add('message', 'Event updated for ' . $event_updated->email);

  header('Location: ' . $_SERVER['HTTP_REFERER']);
}

if ($_POST['action'] == 'cancel') {
  $sql = "UPDATE events_admin SET status=:status WHERE event_id = :event_id";
  $query = $database->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  $query->execute(array(':event_id' => $_POST['id'], ':status' => 'cancelled'));

  $event_updated = EventFactory::create(array(
    'events.id' => $_POST['id']
  ));

  $event_updated->date = $utils->prettyDateFormat($event_updated->date);

  $config = $event_config[$user->isAdmin() ? 'admin' : 'customer'][$event_updated->status];
  if (isset($config['notification'])) {
    Notify::add($config['notification']['type'], $utils->templateString($config['notification']['text'], $event_updated));
  }

  header('Location: /admin/events');
}
?>
