<?php
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
include $_SERVER['DOCUMENT_ROOT'].'/lib/event.php';

$event = EventFactory::create(array(
  'events.id' => $_GET['id']
), true);
?>

<section class="content-section">
  <?php echo $utils->backlink("/admin/events", 'back to admin page'); ?>
  <div class="admin">
    <div class="card mt-4">
      <div class="card-header">
        <div class="row">
          <div class="col-12 col-md-9">
            <strong><?php echo $event->primary_contact; if (!empty($event->secondary_contact)) echo " / " . $event->secondary_contact; ?></strong>
          </div>

          <div class="col-12 col-md-3 admin-actions mt-1">
            <form name="event-update" action="/actions/event" method="post" class="admin-form needs-validation needs-validation-time" novalidate>
              <input type="hidden" name="id" value="<?php echo $event->id; ?>" />
              <input type="hidden" name="admin[booking_type]" value="<?php echo $event->booking_type; ?>" />
              <input type="hidden" name="admin[status]" value="<?php echo $event->status; ?>" />
              <input type="hidden" name="admin[contract_status]" value="<?php echo $event->contract_status; ?>" />
              <input type="hidden" name="event[email]" value="<?php echo $event->email; ?>" />
              <input type="hidden" name="event[primary_contact]" value="<?php echo $event->primary_contact; ?>" />

              <div class="d-grid gap-2 d-md-flex my-2 my-md-0">
                <?php if ($event->status == 'pending') { ?>
                  <input type="hidden" name="admin[status]" value="confirmed" />
                  <button type="submit" name="action" value="update" class="btn btn-success btn-sm flex-fill">Confirm event</button>
                <?php } ?>

                <?php if ($event->status == 'enquiry') { ?>
                  <input type="hidden" name="admin[status]" value="pending" />
                  <button type="submit" name="action" value="update" class="btn btn-success btn-sm flex-fill">Accept enquiry</button>
                <?php } ?>

                <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm confirm-action flex-fill">Cancel event</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="card-body pb-2 pt-2">
        <form name="event-update" action="/actions/event" method="post" class="admin-form needs-validation needs-validation-time" novalidate>
          <div class="container">
            <input type="hidden" name="id" value="<?php echo $event->id; ?>" />

            <?php include ($_SERVER['DOCUMENT_ROOT'].'/templates/event/admin/form.php'); ?>
            <?php include ($_SERVER['DOCUMENT_ROOT'].'/templates/event/form.php'); ?>

            <div class="row text-end">
              <div class="d-grid gap-2 d-md-block mt-2">
                <span class="float-start form-info">* required field</span>
                <button type="submit" name="action" value="update" class="btn btn-primary btn-sm">Update event</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>