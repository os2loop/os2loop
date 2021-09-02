document.querySelector('#drupal-login input[type="submit"]').addEventListener('click', function(event) {
  // Make sure that we keep the fragment id in the form action url.
  // If we don't do this, any login errors will not be visible.
  event.target.form.action = document.location.href
})
