define(['jquery', 'jqueryui'], function ($) {
    return {

        speech: function () {

            function change_button(audio) {

                if (!audio.paused){
                    $('.btn-speech-ply').removeClass('play');
                    $('.btn-note-ply').removeClass('play');
                    $('.btn-speech-ply').addClass('pause');
                    $('.btn-note-ply').addClass('pause');
                } else {
                    $('.btn-speech-ply').removeClass('pause');
                    $('.btn-note-ply').removeClass('pause');
                    $('.btn-speech-ply').addClass('play');
                    $('.btn-note-ply').addClass('play');
                }
            }
            function slowScroll(number) {
                var offset = 200;
                $('html, body').animate({
                    scrollTop: $('[number="' + number + '"]').offset().top - offset
                }, 1000);
                return false;
            }
            function active_element1(idnote) {
                // активация note и создание на нем кнопки

                var lastidnote = $('.btn-note-ply.pause').parent().attr('data-id-note');
                if (!$('.btn-note-ply.pause').length || lastidnote != idnote) {
                    var idNoteS = $('[data-id-note="' + idnote + '"]');
                    $(idNoteS).parents('[data-id-note]').addClass('activetag');
                    if (!$('.btn-note-ply.play').length) {
                        var btn = $('<button class="play btn-outline-dark btn-note-ply" id="button"></button>');
                        $(idNoteS).append(btn);
                    }
                }
            }

            function deactive_element1(click) {
                // деактивация note и удаление на нем кнопки
                if (click) {
                    $('[data-id-note]').parents('[data-id-note]').removeClass('activetag');
                    $('.btn-note-ply').remove();
                } else {
                    $('[data-id-note]').parents('[data-id-note]').removeClass('activetag');
                    $('.btn-note-ply.play').remove();

                }
            }



            function send_last_speech(idnote) {
                $.ajax({
                    url: "ajax_speech.php",
                    type: "POST",
                    dataType: 'json',
                    data: {
                        action: 'saveuser',
                        currentidnote: idnote,
                        coursemoduleid: $.urlParam('id')
                    },
                    async: false
                });
            }

            $.urlParam = function (name) {
                var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
                if (results == null) {
                    return null;
                } else {
                    return decodeURI(results[1]) || 0;
                }
            };

            function set_urls() {
                // добавление в html url на спичи
                var lastSpeech;
                $.ajax({
                    url: "ajax_speech.php",
                    type: "POST",
                    dataType: 'json',
                    async: false,
                    data: {
                        action: 'speechload',
                        coursemoduleid: $.urlParam('id')
                    },
                    success: function(response) {
                        $.each(response.data, function(key, value) {
                            $('[data-id-note="' + value.noteid + '"]').attr('data-audiourl', value.audiourl);
                        });
                        $.each(response.dataUsr, function(key, value) {
                            lastSpeech = value.noteid;
                        });
                    },
                });
                // console.log(lastSpeech);
                return lastSpeech;
            }

            function set_numbers() {
                // добавление нумерации к note'ам
                var i = 1;
                $('[data-id-note]').each(function () {
                    if ($(this).text()) {
                        if ($(this).attr('data-audiourl')) {
                            $(this).attr('number', i);
                            i++;
                        }
                    }
                });
                return i;
            }

            function urls_exists() {
                var result = true;
                $('[data-id-note]').each(function () {
                    // console.log('наличие текста: ' + $(this).text().length);
                    // console.log('отстутствие ссылки: ' + !$(this).attr('data-audiourl'));
                    if ($(this).text().length) {
                        if(!$(this).attr('data-audiourl')) {
                            result =  false;
                        }
                    }
                });
                return result;
            }

            function download_speech(idnote, cnt) {
                // закачка спича и добавление url в html
                // console.log('Старт закачки спича');
                var speechtext = ($('[data-id-note="'+idnote+'"]').text()); //TODO: условие
                // console.log('speechtext: ' + speechtext);
                $.ajax({
                    type: "GET",
                    async: true,
                    url: "../longread/speech.php",
                    data: { action: "SendData", SendText: speechtext, SendNoteid: idnote, moduleid: $.urlParam('id')},
                    success: function (url) {
                        // console.log('Подстановка ссылки: ' + url + ' idnote: ' + idnote);
                        $('[data-id-note="'+idnote+'"]').attr('data-audiourl', url);
                        if (cnt === 1){
                            $('.btn-speech-ept').removeClass('lds-dual-ring-sch');
                            $('.btn-speech-ept').addClass('play');
                            $('.btn-speech-ept').addClass('btn-speech-ply');
                            $('.btn-speech-ply').attr('number', 1);
                        }
                    },
                });
            }

            function change_audio(audio, number) {
                //TODO: заменить на get_idnote()
                var idnote = $('[number="' + number + '"]').attr("data-id-note");
                send_last_speech(idnote);
                $('.btn-speech-ply').attr('globalNumber', number);
                $('.btn-speech-ply').attr('number', number);
                audio.src = $('[number="' + number + '"]').attr("data-audiourl");
                return audio;
            }

            function get_idnote(number){
                return $('[number="' + number + '"]').attr("data-id-note");
            }

            function get_number(idnote) {
                return $('[data-id-note="' + idnote + '"]').attr("number");
            }

            function play_play(audio, SchObj, number, lastElement) {
                audio = change_audio(audio, number);
                audio.play();
                change_button(audio);

                $('.btn-speech-ply').attr('number', number);
                $(audio).on('ended', function () {
                    var idnote = get_idnote(number);
                    deactive_element1(true);
                    //TODO: проверка завершения
                    if (number != lastElement) {
                        number++;
                        idnote = get_idnote(number);
                        active_element1(idnote);
                        audio = change_audio(audio, number);
                        $('.btn-speech-ply').attr('number', number);
                        audio.play();
                        change_button(audio);
                    }
                });
                // return [audio, SchObj, number];
            }

            var lastElement;
            function download_all_speeches() {
                var btn_spch = $('.btn-speech-ept');
                btn_spch.removeClass('play');
                btn_spch.addClass('lds-dual-ring-sch');
                //Закачка всех спичей
                var all = $('[data-id-note]').length;
                var current = 0;
                var cnt = 0;
                $('[data-id-note]').each(function () {
                    current++;
                    // console.log(all);
                    // console.log(current);
                    if(all === current) cnt = 1;
                    if ($(this).text().length && !$(this).attr("data-audiourl")) {
                        download_speech($(this).attr('data-id-note'), cnt);
                        $(this).attr('number', lastElement);
                        lastElement++;
                    }
                    //присвоение прогрессбару current/all*100
                });
            }

            $(document).ready(function() {
                var lastSpeech = set_urls();
                if (!urls_exists()) {
                    $('.btn-speech-ply').addClass('btn-speech-dwn');
                    $('.btn-speech-ply').removeClass('btn-speech-ply');
                }
                var audio = document.getElementById('audio');
                var SchObj = {};

                $(document).on('click', '.btn-speech-dwn', function() {

                    $(this).removeClass('btn-speech-dwn');

                    download_all_speeches();

                });

                lastElement = set_numbers();

                if (!lastSpeech) {
                    $('.btn-speech-ply').attr('number', 1);
                } else {
                    var lastNumber = get_number(lastSpeech);
                    $('.btn-speech-ply').attr('number', lastNumber);
                }

                $('[data-id-note]').mouseenter(function () {
                    var idnote = $(this).attr('data-id-note');
                    if($(this).attr('data-audiourl')) {
                        active_element1(idnote);
                    }
                    var target = $(this).parents('[data-id-note]').last();
                    if (!target.length) target = $(this);
                    if (!target.find(SchObj.btnadd).length){
                        if(target.children('.btn-note-ply').length == 0) {
                            target.append(SchObj.btnadd);
                        }
                    }
                    target.one('mouseleave', function() {
                        deactive_element1();
                    });
                });

                $(document).on('click', '.btn-note-ply', function() {
                    var number = get_number($(this).parent().attr('data-id-note'));
                    $('.btn-speech-ply').attr('globalNumber', number);
                    $('.btn-speech-ply').click();
                });

                $(document).on('click', '.btn-speech-ply', function() {
                    var globalNumber = $(this).attr('globalNumber');
                    if(!globalNumber) {
                        globalNumber = $('.btn-speech-ply').attr('number');
                    }

                    if (globalNumber == $(this).attr('number')) {
                        if (audio.src) {
                            if (audio.paused) {
                                audio.play();
                                change_button(audio);
                            } else {
                                audio.pause();
                                change_button(audio);
                            }
                        } else {
                            slowScroll(globalNumber);
                            active_element1(get_idnote(globalNumber));
                            play_play(audio, SchObj, globalNumber, lastElement);
                        }
                    } else {
                        $('.btn-note-ply.pause').parent().removeClass('activetag');
                        $('.btn-note-ply.pause').remove();
                        if (!globalNumber) {
                            globalNumber = $(this).attr('number');
                            slowScroll(globalNumber);
                            active_element1(get_idnote(globalNumber));
                            play_play(audio, SchObj, globalNumber, lastElement);
                        } else {
                            play_play(audio, SchObj, globalNumber, lastElement);
                        }
                    }
                });
            });
        }
    };
});
