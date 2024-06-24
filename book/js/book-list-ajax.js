jQuery(document).ready(function ($) {
  var offset = 4;
  var $loadMoreButton = $("#load-more-books");

  $loadMoreButton.on("click", function () {
    $loadMoreButton.find(".fa-spin").css("display", "inline-block");
    $.ajax({
      url: bookListAjax.ajaxurl,
      type: "post",
      data: {
        action: "load_more_books",
        offset: offset,
      },
      success: function (response) {
        var $response = $(response);
        $loadMoreButton.find(".fa-spin").hide();
        var $books = $response.find(".book-card");
        $("#book-list").append(response);
        offset += 4;
        if ($books.length < 4) {
          $loadMoreButton.hide();
        }
      },
    });
  });
});
