(function(window, $) {
    $(function(){

        var $body       =   $(document.body).on("touchstart", function(){}),
            $emailEls   =   $('a.email');

        $.each($emailEls, function( k, v ){
            var $this           =   $(this),
                $href           =   $this.attr("href"),
                $searchFor      =   "email@email.com",
                $replaceWith    =   "hello@zachwolf.com";

            $this.attr("href", $href.replace($searchFor, $replaceWith));

            if($this.hasClass("keep-text")) return;

            $this.html($replaceWith);
        });

        $(".sexy-placeholder")
            .sexyPlaceholder();

        $.fn.sexyPlaceholder = function($params) {

            var $targets    =   $(this),
                $color      =   $params.color || "red";

            $.each($targets, function( k, v ){
                var $target         =   $(v),
                    $targetVal      =   $target.html(),
                    $placeholder    =   $target.attr("placeholder"),
                    $targetStyles   =   {
                                            "background": $target.css("background"),
                                            "margin"    : $target.css("margin")
                                        };

                $target
                    .attr("placeholder", "")
                    .wrap($("<div></div>", {
                        "class" :   "placeholder-wrap",
                        css     :   {
                                        "position"      :   "relative",
                                        "background"    :   $targetStyles.background,
                                        "margin"        :   $targetStyles.margin,
                                        "z-index"       :   0,
                                        "overflow"      :   "hidden"
                                    }
                        })
                    )
                    .css({
                        "background"    :   "transparent",
                        "position"      :   "relative",
                        "margin"        :   0,
                        "z-index"       :   2
                    })
                    .before($('<span></span>', {
                        html        :   $placeholder,
                        css         :   {
                                            "position"  :   "absolute",
                                            "z-index"   :   1,
                                            top         :   10,
                                            "margin-left":  10
                                        }
                    }))
                    .on("focus", function( e ){
                        var $span   =   $(this).siblings("span");

                        if($(this).val().length === 0 ){
                            $span
                                .animate({
                                    "opacity": 0.5
                                }, 150);
                        }
                    })
                    .on("keydown change", function( e ){
                        var $span   =   $(this).siblings("span");

                        if(e.keyCode !== 9) {
                            $span
                                .animate({
                                    "opacity": 0,
                                    "margin-left":  -10
                                }, 150);
                        }
                    })
                    .on("blur", function( e ){
                        var $span   =   $(this).siblings("span");

                        if($(this).val().length === 0 ){
                            $span
                                .animate({
                                    "opacity": 1,
                                    "margin-left":  10
                                }, 150);
                        }
                    });

                if($targetVal.length > 0) {
                    $target
                        .find("span")
                        .css({
                            "margin-left": "-20px",
                            opacity: 0
                        });
                }
            });

            return this;
        };
    });
})(window, jQuery);