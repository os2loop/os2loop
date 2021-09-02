/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./app.scss";

// Import popper.js
import "popper.js";

// Specific boostrap js
import "bootstrap/js/dist/collapse";
import "bootstrap/js/dist/dropdown";
import "bootstrap/js/dist/tab";
import "bootstrap/js/dist/alert";
import "bootstrap/js/dist/tooltip";

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from "jquery";

// Enable tooltips on icon buttons.
// Imported $ above to make this work.
$(".icon-container .icon").tooltip();

jQuery(() => {
  // Add/remove search-api-autocomplete-has-suggestions class when showing/hiding search autocomplete suggestions.
  jQuery("[data-autocomplete-path]")
    .on("autocompleteopen", (event) =>
      jQuery(event.target)
        .parent()
        .addClass("search-api-autocomplete-has-suggestions")
    )
    .on("autocompleteclose", (event) =>
      jQuery(event.target)
        .parent()
        .removeClass("search-api-autocomplete-has-suggestions")
    );
});
