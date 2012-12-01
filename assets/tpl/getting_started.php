<?php

include('header.php');
?>

  <div class="navbar navbar navbar-fixed-top">
    <div class="navbar-inner">
      <div class="container">
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>
        <a class="brand" href="#">Account Picker</a>
        <div class="nav-collapse collapse">
          <ul class="nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">Documentation</a></li>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
  </div>


    <div class="container">
      <div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">General</li>
              <li><a href="#">Dashboard</a></li>
              <li><a href="#">Users</a></li>
              <li class="nav-header">Integration</li>
              <li class="active"><a href="#">Getting Started</a></li>
              <li><a href="#">Manage</a></li>
              <li><a href="#">API Keys</a></li>
              <li><a href="#">Integration</a></li>
              <li class="nav-header">Help</li>
              <li><a href="#">Documentation</a></li>
              <li><a href="#">Privacy</a></li>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
        <div class="span9">
          <div class="hero-unit" style='text-align:center;'>
            <div class='container'></div>
            <h1>Quick Start Guide</h1>
            <h2>1) Select your app name</h2>
            <form>
              <input type='text' placeholder='App Name' onkeyup='textFieldDidChange()'>
            </form>
            <h2>2) Select your platforms</h2>
              <div class='platform-icon' id='fb-icon' onclick='selectIcon(this)'></div>
              <div class='platform-icon' id='twitter-icon' onclick='selectIcon(this)'></div>
              <div class='platform-icon' id='linkedin-icon' onclick='selectIcon(this)'></div>
              <div class='platform-icon'></div>
              <div class='platform-icon'></div>
              <div class='platform-icon'></div>

            <p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
            <p><a class="btn btn-primary btn-large">Next &raquo;</a></p>
          </div>
        </div><!--/span-->
      </div><!--/row-->

      <hr>

<script>
  var delay;

  function textFieldDidChange() {

    if(!delay){
        delay = setTimeout(function() {
        trySearchName();
        delay = null;
      }, 500);
    }
    else {
      clearTimeout(delay);
      delay = null;
    }
}

  function trySearchName(){
    testFacebookName();
  }

  function testFacebookName(){
    addSpinner('fb-icon');
    setTimeout(function() {
        document.getElementsByClassName('loading-icon')[0].src = 'http://www.tonyneihoff.com/elements/images/design/green-checkmark.png';
    }, 500);
  }

  function selectIcon(e){
    e.style.border = '4px green solid';
    e.style.margin = '2px';
  }

  function addSpinner(e){
    var spinner = document.createElement('img');
    spinner.src = 'http://jimpunk.net/Loading/wp-content/uploads/loading6.gif';


    var el = document.getElementById(e);

    el.appendChild(spinner);
  }

</script>

<?php 
  include('footer.php');
?>


