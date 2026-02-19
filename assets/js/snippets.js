// sliders on hover
var timer;
var j = 0;
function activeLoop(el) {
  $(el).removeClass('active');
  $(el[j]).addClass('active');
  j++;
  if (j >= el.length) {
    j = 0;
  }
}

$('.post-type-archive-workbench .gallery').mouseenter(function() {
  var imgs = $(this).find('.gallery-img');

  activeLoop(imgs);
  if (imgs.length > 1) {
    timer = setInterval(function() {
      activeLoop(imgs);
    }, 1000);
  }
}).mouseleave(function() {
  if (timer) {
    clearInterval(timer);
    timer = undefined;
  }
});