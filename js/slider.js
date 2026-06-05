$(document).ready(function(){
    var $slider = $('.slider-gallery').slick({
       slidesToShow: 1,
        slidesToScroll: 1,
        dots: false, 
        infinite: false,
        arrows: true,
        responsive: [
            {
                breakpoint: 1024, 
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: false,
                    dots: false
                }
            },
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1, 
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 600, 
                settings: {
                    slidesToShow: 1, 
                    slidesToScroll: 1
                }
            }
        ]
    });

    var slideCount = $slider.slick('getSlick').slideCount;

    var $pageInfo = $('<div class="slider-page-info"></div>').appendTo($('.reviews-block_slaider'));

    function formatPageNumber(number) {
        return (number < 10 ? '0' : '') + number;
    }

    function updatePageInfo(currentSlide) {
        var currentPage = currentSlide + 1; 
        $pageInfo.text(formatPageNumber(currentPage) + '/' + formatPageNumber(slideCount));
    }

    updatePageInfo(0);

    $slider.on('afterChange', function(event, slick, currentSlide){
        updatePageInfo(currentSlide);
    });
});
