define(['jquery', 'mod_longread/owl.carousel', 'mod_longread/rslides', 'mod_longread/colorpicker', 'jqueryui', 'touch_punch'], function ($, owl, Rslides, jui, touch_punch) {
    function doAddTags(tag1, tag2, obj) {
        // Code for IE
        if (document.selection) {
            obj.focus();
            var sel = document.selection.createRange();
            sel.text = tag1 + sel.text + tag2;
        } else {  // Code for Mozilla Firefox
            var len = obj.value.length;
            var start = obj.selectionStart;
            var end = obj.selectionEnd;

            var scrollTop = obj.scrollTop;
            var scrollLeft = obj.scrollLeft;


            var sel = obj.value.substring(start, end);
            var rep = tag1 + sel + tag2;
            obj.value = obj.value.substring(0, start) + rep + obj.value.substring(end, len);

            obj.scrollTop = scrollTop;
            obj.scrollLeft = scrollLeft;
        }
    }

    return {
        init: function () {
            var module = this;

            $(function () {
                // menu
                $('.longread-fixed-menu-wrapper .menu-button').click(function () {
                    $(this).parent().toggleClass('closed');
                });

                // slides
                $('.mod_longread-slider .owl-carousel').owlCarousel({
                    nav: true,
                    navText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>'],
                    items: 1,
                    responsiveClass: true,
                    autoHeight: true,
                    lazyLoad:true,
                    lazyLoadEager: 2
                    // , loop:true // бесконечно листать по кругу
                });

                // modal
                // $('.longread-wrapper .img-container[data-modal]').click(function () {
                //     var modal = $(this).data('modal');
                //     $(modal).show();
                // });

                // document.querySelector(".modal-img img").addEventListener("wheel",function(e){
                //     e.preventDefault();
                //     if(e.wheelDelta < 0){
                //         var newHeight = $(".modal-img img").height() - 20;
                //         $(".modal-img img").height(newHeight);
                //     } else if(e.wheelDelta > 0){
                //         var newHeight = $(".modal-img img").height() + 20;
                //         $(".modal-img img").height(newHeight);
                //     }
                // });

                // $('.longread-wrapper .modal-bg .close').click(function () {
                //     $(this).parent('.modal-bg').hide();
                // });

                // flip cards
                $('.longread-wrapper .flip-card').click(function () {
                    $(this).toggleClass('turn');
                });

                // multitabs
                if ($('.mod_longread-multitab').length) {
                    $('.mod_longread-multitab').each(function () {
                        $('.rslides', this).responsiveSlides({
                            auto: false,
                            manualControls: $('.controls', this)
                        });
                    });
                }

                // matching tests
                $('.longread-wrapper .mod_longread-matching').each(function () {
                    var moduleID = $('input[name="id"]', this).val();

                    $('.longread-wrapper .mod_longread-matching .matching-sortable-' + moduleID).sortable({
                        connectWith: '.longread-wrapper .mod_longread-matching .matching-sortable-' + moduleID,
                        items: '.answer',
                        update: function (e, ui) {
                            if ($(this).parent().hasClass('matching')) {
                                var form = $(this).parents('form'),
                                    result = [];

                                $('.matching>div', form).each(function () {
                                    $('.answer', this).each(function () {
                                        result.push($(this).data('id'));
                                    });
                                });

                                $('input[name="answer"]', form).val(JSON.stringify(result));
                            }
                        }
                    }).disableSelection();
                });

                // order tests mod_longread-set-in-order
                $('.longread-wrapper .set-in-order-sortable').sortable({
                    update: function (e, ui) {
                        var form = $(this).parents('form'),
                            result = [];

                        $('label', this).each(function () {
                            result.push($(this).data('id'));
                        });

                        $('input[name="answer"]', form).val(JSON.stringify(result));
                    }
                }).disableSelection();

                // words in cards
                $('.mod_longread-fill-blank-words-cards .owl-carousel').owlCarousel({
                    items: 3,
                    nav: true,
                    navText: ['', ''],
                    center: true,
                    responsiveClass: true,
                    responsive: {
                        0: {
                            items: 1,
                            nav: false
                        },
                        767: {
                            items: 1,
                            nav: false
                        },
                        1201: {
                            items: 3,
                            nav: true
                        }
                    }
                });

                // choice by cards
                $('.longread-wrapper .mod_longread-choice-by-cards').each(function () {
                    var moduleID = $('input[name="id"]', this).val();

                    $('.longread-wrapper .mod_longread-choice-by-cards .matching-sortable-' + moduleID).sortable({
                        connectWith: '.longread-wrapper .mod_longread-choice-by-cards .matching-sortable-' + moduleID,
                        items: '.answer',
                        update: function (e, ui) {
                            if ($(this).parent().hasClass('matching')) {
                                var form = $(this).parents('form'),
                                    result = {};

                                $('.matching>div', form).each(function () {
                                    var index = null,
                                        match = [];

                                    index = $(this).data('id').toString();

                                    $('.answer', this).each(function () {
                                        match.push($(this).data('id'));
                                    });

                                    result[index] = match;
                                });

                                $('input[name="answer"]', form).val(JSON.stringify(result));
                            }
                        }
                    }).disableSelection();
                });

                // очистить поля для ввода ответов
                $('.mod_longread-fill-blank-words form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var datamod = $(this).attr('data-mod');
                        var inpt = $('div .answers input[data-target|="answer-' + datamod + '"]');
                        inpt.removeAttr('disabled');
                        inpt.val('');
                    }
                });
                $('.mod_longread-fill-blank-words-cards form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var datamod = $(this).attr('data-mod');
                        var inpt = $('.answers div[data-target|="answer-' + datamod + '"] input[name^="answer"]');
                        inpt.val('');
                        inpt.removeAttr('disabled');
                    }
                });
                $('.mod_longread-matching-pics-words form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'auto');
                        $('img.answer', form).css('pointer-events', 'auto');
                    }else{
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'none');
                        $('img.answer', form).css('pointer-events', 'none');
                    }
                });

                $('.mod_longread-matching-words form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'auto');
                    }else{
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'none');
                    }
                });
                $('.mod_longread-choice-by-cards form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'auto');
                    }else{
                        var form = $(this).parents('form');
                        $('span.answer', form).css('pointer-events', 'none');
                    }
                });
                $('.mod_longread-set-in-order form>button.btnAnswer').click(function () {
                    if ($(this).attr('data-rep') == 1) {
                        var form = $(this).parents('form');
                        $('label.setinord', form).css('pointer-events', 'auto');
                    }else{
                        var form = $(this).parents('form');
                        $('label.setinord', form).css('pointer-events', 'none');
                    }
                });


                //longread forms
                $('.longread-wrapper form>button').click(function () {
                    var form = $(this).parents('form'),
                        data = form.serialize();

                    var btnrep = $('button.btnAnswer', form).attr('data-rep');

                    if (btnrep == 0) {
                        $.post('/mod/longread/ajax.php', data, function (resp) {
                            var answerTypes = [
                                'choice',
                                'right',
                                'wrong'
                            ];
                            if (typeof resp === 'object' && resp !== null) {
                                if (resp.hasOwnProperty('msg'))
                                    alert(resp.msg);
                                else {
                                    $('button.btnAnswer', form).html('Попробовать еще раз');
                                    $('button.btnAnswer', form).attr('data-rep', 1);
                                }
                                if (resp.hasOwnProperty('data')) {
                                    $('button', form).attr('disabled', true);
                                    $('button', form).css('cursor', 'wait');
                                    $('button', form).css('background}', '#8181bd');

                                    setTimeout(function () {
                                        $('button', form).removeAttr('disabled');
                                        $('button', form).css('cursor', 'pointer');
                                        $('button', form).css('background}', '#6162ac');
                                    }, 3000);

                                    answerTypes.forEach(function (type) {
                                        if (typeof (resp.data[type]) !== 'undefined') {
                                            resp.data[type].forEach(function (elem) {
                                                var targetElem = $('[data-target="' + elem + '"]', form);
                                                targetElem.addClass(type);
                                                if (targetElem.data('label') === true)
                                                    targetElem.append('<span class="' + type + '"></span>');
                                            });
                                        }
                                    });

                                }
                            }
                        });
                    } else if (btnrep == 1) {
                        var answerTypes = ['choice', 'right', 'wrong'];
                        answerTypes.forEach(function (type) {
                            var targetElem = $('.' + type, form);
                            targetElem.removeClass(type);
                            targetElem.find('span .' + type).remove();
                        });
                        $('button.btnAnswer', form).html('Проверить');
                        $('button.btnAnswer', form).attr('data-rep', 0);
                    }
                });


                $(document).ready(function () {
                    // время чтения
                    var textScreen = document.getElementsByClassName('mod_longread-screen');
                    var cntWord = 0, cntSymbol = 0, ctnImg = 0, timeRead = 0;
                    $.each(textScreen, function (key, value) {
                        var s = value.innerText;
                        s = s.replace(/\u00A0/g, '').replace('\t', '').replace(/\r\n?|\n/g, ' ').replace(/ {2,}/g, ' ').replace(/^ /, '').replace(/ $/, '');
                        cntSymbol += s.length;
                        cntWord += s.split(' ').length;
                    });

                    let treadingAll = $('[data-treading]');
                    let treading = 0;

                    treadingAll.each(function () {
                        let k = $(this).attr('data-treading');
                        treading += Number(k);
                    });

                    ctnImg += $('.mod_longread-screen img').length;
                    timeRead = Math.ceil(cntSymbol / 1500 + treading / 60);
                    // console.log(timeRead + ' = ' + cntSymbol + '/ 1500 + ' + treading/60);

                    var declOfNum = function(number, titles)
                    {
                        var  cases = [2, 0, 1, 1, 1, 2];
                        return titles[
                            (number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]
                            ];
                    }

                    if(timeRead > 60){
                        let hours = Math.trunc(timeRead/60);
                        let minutes = timeRead % 60;
                        var strTimeRead = hours + ' ' + declOfNum(hours, ['час', 'часа', 'часов']) + ' '
                            + minutes + ' ' + declOfNum(minutes, ['минута', 'минуты', 'минут']);
                    } else var strTimeRead = timeRead + ' ' + declOfNum(timeRead, ['минута', 'минуты', 'минут']);

                    if ($('.timeread').length > 0) {
                        // $('.mod_longread-page-header .text').html($('.mod_longread-page-header .text').html().replace('$$',strTimeRead));
                        $('.timeread').html(strTimeRead);
                    }

                });


                // table order
                module.init_sort();
            });
        },
        init_sort: function () {
            $('.local-table-sortable tbody').sortable({
                handle: '.local-table-move',
                update: function (e, ui) {
                    var data = {
                        ids: [],
                        action: ''
                    };

                    if ($('.local-table-move').length) {
                        $('.local-table-move').each(function () {
                            data['ids'].push($(this).data('id'));
                        });

                        data['action'] = $('.local-table-move:eq(0)').data('action');

                        $.post('/mod/longread/ajax.php', data, function (resp) {
                            window.location.reload();
                        });
                    }
                }
            }).disableSelection();
        },
        set_oneline_wysiwyg: function () {
            if ($('input[data-oneline-wysiwyg]').length) {
                $('input[data-oneline-wysiwyg]').each(function () {
                    $(this).before('<div class="mb-1"><button type="button" class="btn btn-primary mr-1 oneline-wysiwyg-b">Выделить</button><button type="button" class="btn btn-info oneline-wysiwyg-g">Глоссарий</button></div>');
                });

                $(document).on('click', '.oneline-wysiwyg-b', function () {
                    doAddTags('¿', '¿', $(this).parent().parent().find('input[data-oneline-wysiwyg]')[0]);
                });

                $(document).on('click', '.oneline-wysiwyg-g', function () {
                    doAddTags('|', '|', $(this).parent().parent().find('input[data-oneline-wysiwyg]')[0]);
                });
            }
        },
        initColorPicker: function() {
        // let ci = 0;
            // $(".colorPicker").each(function () {
            //     ci++;
                //todo цвет выбирается только у второго инпута
                $("input[name='color1']").ColorPicker({
                    onSubmit: function(hsb, hex, rgb, el) {
                        $(el).val(hex);
                        $(el).ColorPickerHide();
                    },
                    onBeforeShow: function () {
                        $(this).ColorPickerSetColor(this.value);
                    },
                    onChange: function (hsb, hex, rgb, el) {
                        $("input[name='color1']").val(hex);
                    }
                });

            $("input[name='color2']").ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onChange: function (hsb, hex, rgb, el) {
                    $("input[name='color2']").val(hex);
                }
            });
            // })

        }
    }
});