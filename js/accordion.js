$(document).ready(function() {
  $(".faq-question").click(function() {
    $(this).toggleClass("active").next(".faq-answer").slideToggle();
    $(".faq-question").not(this).removeClass("active").next(".faq-answer").slideUp();
  });
});

