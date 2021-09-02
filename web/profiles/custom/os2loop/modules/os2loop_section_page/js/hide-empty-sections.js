Drupal.behaviors.hideEmptySections = {
  attach: function (context, settings) {
    let empty_views = document.getElementsByClassName("js-view-empty");
    // Act on each empty view.
    for (let i = 0; i < empty_views.length; i++) {
      let el = empty_views.item(i);
      // Look for class section in parents.
      let section = findUpClass(el, 'section');
      if (section) {
        // Add explanatory class.
        section.classList.add("js-empty-list");

        // Hide element.
        section.classList.add("d-none");
      }
    }
  }
};

// Iterate up through parents looking for a class.
function findUpClass(el, tag) {
  while (el.parentNode) {
    el = el.parentNode;
    let classList = el.className.split(' ')
    if (classList.includes(tag))
      return el;
  }
  return null;
}
