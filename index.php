<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . '/lib/common.php');

require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
$auth = new \Delight\Auth\Auth($database, null);

include ('lib/auth/session.php');
include ('lib/utils.php');

require ($_SERVER['DOCUMENT_ROOT'].'/lib/auth/facebook/auth.php');
require ($_SERVER['DOCUMENT_ROOT'].'/lib/auth/google/auth.php');

include ($_SERVER['DOCUMENT_ROOT'].'/config/regions.php');
$region = $regions[$_GET['region']]["db_key"];
$region_url_prefix = isset($_GET['region']) && $_GET['region'] != "leeds" ? "/" . $_GET['region'] : "";

include ($_SERVER['DOCUMENT_ROOT'].'/config/djs.php');
?>

<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $regions[$_GET['region']]["page_title"]; ?></title>
    <meta name="google-site-verification" content="YM9Q2_7XH7lNb7DORpCx4tWHBF1xRrpZi4jSL48HKbk" />
    <meta name="description" content="<?php echo $regions[$_GET['region']]["page_description"]; ?>" />
    <meta name="robots" content="index,follow" />
    <meta name="copyright" content="Copyright &copy; <?php echo date("Y"); ?> Mark Hepworth. All Rights Reserved." />
    <meta name="author" content="Mark Hepworth" />
    <meta name="generator" content="www.onlinemetatag.com" />
    <meta name="revisit-after" content="7" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="google-site-verification" content="PtaoFl3G-HN3cqu5PcRQEIkF-7cf38pXr8SUR0XrhVs" />
    <meta name="google-signin-client_id" content="911426234941-948u7fg8cdv9mlia89eoann478i58bhn.apps.googleusercontent.com">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lekton:400" />
    <link rel="icon" href="/assets/images/favicon.ico" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- Custom styles -->
    <link href="/assets/production/hilife.min.css" rel="stylesheet" />
    <link href="/assets/css/gallery/gallery-minified.css" rel="stylesheet" />

    <script src="https://kit.fontawesome.com/51da9c5fc0.js" crossorigin="anonymous"></script>

    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <script defer src="/assets/javascript/lib/photoswipe.js"></script>
    <script defer src="/assets/production/hilife.min.js"></script>

    <?php if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 'accepted') { ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-171512010-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-171512010-1');
    </script>
    <?php } ?>
  </head>

  <body>
    <?php if (isset($_COOKIE['cookie-consent']) && $_COOKIE['cookie-consent'] == 'accepted') { ?>
    <!-- Google Tag Manager -->
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TDZP2H"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TDZP2H');</script>
    <!-- End Google Tag Manager -->
    <?php } ?>

    <header>
      <div class="navigation-container">
        <?php include ('templates/navigation.php'); ?>
        <div class="header-fade"></div>
      </div>
    </header>

    <main role="main">
    <?php include ('templates/notification.php'); ?>
      <div class="container">
        <?php include ('pages/'.$_GET['page_name'].'.php'); ?>
      </div>
    </main>

    <?php include ('templates/cookies.php'); ?>

    <footer>
      <div class="footer-fade"></div>
      <?php include ('templates/footer.php'); ?>
    </footer>
  </body>
</html>
