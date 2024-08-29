$(document).ready(function () {
    $('#logoutButton').on('click', function (e) {
      e.preventDefault();
  
      // Perform AJAX request to logout.php to clear the session
      $.ajax({
        url: 'shared/logout.php',
        method: 'POST',
        success: function (response) {
          // Redirect to login.php after successful logout
          window.location.href = 'login.php';
        },
        error: function () {
          alert('Logout failed. Please try again.');
        }
      });
    });
  });
  