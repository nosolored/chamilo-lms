<div id="learning_path_main" class="{{ is_allowed_to_edit ? 'lp-view-include-breadcrumb' }} {{ lp_mode == 'embedframe' ? 'lp-view-collapsed' : '' }}">
    {% if show_left_column == 1 %}
    <div id="learning_path_left_zone" class="sidebar-scorm">
        <div class="lp-view-zone-container">
            <div id="scorm-info">
                <div id="panel-scorm" class="panel-body">
                    <div class="image-avatar">
                        {% if lp_author == '' %}
                           <div class="text-center">
                                {{ lp_preview_image }}
                            </div>
                        {% else %}
                            <div class="media-author">
                                <div class="media-author-avatar">
                                    {{ lp_preview_image }}
                                </div>
                                <div class="media-author-description">
                                    {{ lp_author }}
                                </div>
                            </div>
                        {% endif %}
                    </div>
                    {% if show_audio_player %}
                        <div id="lp_media_file" class="audio-scorm">
                            {{ media_player }}
                        </div>
                    {% endif %}

                    {% if lp_accumulate_work_time != '' %}
                        {% set lp_progress %}
                        <style>
                            #timer .container{display:table;background:#777;color:#eee;font-weight:bold;width:100%;text-align:center;text-shadow:1px 1px 4px #999;}
                            #timer .container div{display:table-cell;font-size:24px;padding:0px;width:20px;}
                            #timer .container .divider{width:10px;color:#ddd;}
                        </style>
                        <script>
                            $(function() {
                                var timerData = {
                                    hour: parseInt($("#hour").text()),
                                    minute: parseInt($("#minute").text()),
                                    second:  parseInt($("#second").text())
                                };
                                clearInterval(window.timerInterval);
                                window.timerInterval = setInterval(function(){
                                    // Seconds
                                    timerData.second++;
                                    if (timerData.second >= 60) {
                                        timerData.second = 0;
                                        timerData.minute++;
                                    }

                                    // Minutes
                                    if (timerData.minute >= 60) {
                                        timerData.minute = 0;
                                        timerData.hour++;
                                    }

                                    $("#hour").text(timerData.hour < 10 ? '0' + timerData.hour : timerData.hour);
                                    $("#minute").text(timerData.minute < 10 ? '0' + timerData.minute : timerData.minute);
                                    $("#second").text(timerData.second < 10 ? '0' + timerData.second : timerData.second);
                                }, 1000);
                            })
                        </script>
                        <div class="row">
                            <div class="col-xs-4">
                                <b>
                                    {{ "ProgressSpentInLp"|get_lang|format(lp_accumulate_work_time) }}
                                </b>
                            </div>
                            <div class="col-xs-8">
                                <div id="progress_bar">
                                    {{ progress_bar }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4">
                                <b>
                                    {{ "TimeSpentInLp"|get_lang|format(lp_accumulate_work_time) }}
                                </b>
                            </div>
                            <div class="col-xs-8">
                                <div id="timer">
                                    <div class="container">
                                        <div id="hour">{{ hour }}</div>
                                        <div class="divider">:</div>
                                        <div id="minute">{{ minute }}</div>
                                        <div class="divider">:</div>
                                        <div id="second">{{ second }}</div>
                                        <div id="slash"> / </div>
                                        <div>{{ hour_min }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {% endset %}
                    {% else %}
                        {% set lp_progress %}
                            <div id="progress_bar">
                                {{ progress_bar }}
                            </div>
                        {% endset %}
                    {% endif %}

                    {% if gamification_mode == 1 %}
                        <!--- gamification -->
                        <div id="scorm-gamification">
                            <div class="row">
                                <div class="col-xs-6">
                                    {% if gamification_stars > 0 %}
                                        {% for i in 1..gamification_stars %}
                                            <em class="fa fa-star level"></em>
                                        {% endfor %}
                                    {% endif %}

                                    {% if gamification_stars < 4 %}
                                        {% for i in 1..4 - gamification_stars %}
                                            <em class="fa fa-star"></em>
                                        {% endfor %}
                                    {% endif %}
                                </div>
                                <div class="col-xs-6 text-right">
                                    {{ "XPoints"|get_lang|format(gamification_points) }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 navegation-bar">
                                   {{ lp_progress }}
                                </div>
                            </div>
                        </div>
                        <!--- end gamification -->
                    {% else %}
                       {{ lp_progress }}
                    {% endif %}

                    {{ teacher_toc_buttons }}
                </div>
            </div>
            {# TOC layout #}
            <div id="toc_id" class="scorm-body" name="toc_name">
                {# div#flab-mobile is to know when the user is on mobile view. Don't delete. #}
                <div id="flag-mobile" class="visible-xs-block" aria-hidden="true"></div>
                {% include 'learnpath/scorm_list.tpl'|get_template %}
            </div>
            {# end TOC layout #}
        </div>
    </div>
    {# end left zone #}
    {% endif %}

    {# Right zone #}
    <div id="learning_path_right_zone" class="{{ show_left_column == 1 ? 'content-scorm' : 'no-right-col' }}">
        {% if data_list_app is not empty %}
        <!-- NSR -->
        <style>
            #box-message-app {
              height: 300px;
              /*IMPORTANTE*/
              display: flex;
              justify-content: center;
              align-items: center;
            }

            .hijo {
              background: white;
              z-index: 10;
              /*width: 120px;*/
            }
        </style>
        <div id="box-message-app" style="padding:15px;">
            <div class="col-xs-12 navegation-bar text-primary hijo" style="font-weight: bold; border: 2px solid; line-height: 40px; padding: 5px 15px 5px 15px; font-size: 15px;">
                Pulsa sobre las flechas <img src="/main/img/icons/48/icon_flechas.png" /> para navegar por las diferentes lecciones.<br>
                Pulsa sobre el icono de la casa <img src="/main/img/icons/48/icon_home.png" /> cuando termines o decidas salir de las lecciones.<br>
                <input type="button" id="btn-entendido" class="btn btn-primary" value="Entendido" />
            </div>
        </div>
        <script>
            $(function() {
                $("#btn-entendido").on('click', function (e) {
                    e.preventDefault();
                    $("#box-message-app").hide(); 
                });
                
            });
        </script>
        {% endif %}
        <div class="lp-view-zone-container">
            <div class="lp-view-tabs">
                <div id="navTabsbar" class="nav-tabs-bar">
                    <ul id="navTabs" class="nav nav-tabs tabs-right" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#lp-view-content" title="{{ 'Lesson'|get_lang }}"
                               aria-controls="lp-view-content" role="tab" data-toggle="tab">
                                <span class="fa fa-book fa-2x fa-fw" aria-hidden="true"></span>
                                <span class="sr-only">{{ 'Lesson'|get_lang }}</span>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#lp-view-forum" title="{{ 'Forum'|get_lang }}"
                               aria-controls="lp-view-forum" role="tab" data-toggle="tab">
                                <span class="fa fa-commenting-o fa-2x fa-fw" aria-hidden="true"></span>
                                <span class="sr-only">{{ 'Forum'|get_lang }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                {% include 'learnpath/menubar.tpl'|get_template %}

                <div id="tab-iframe" class="tab-content">

                    <div role="tabpanel" class="tab-pane active" id="lp-view-content">
                        <div id="wrapper-iframe">
                        {% if lp_mode == 'fullscreen' %}
                            <iframe
                                id="content_id_blank"
                                name="content_name_blank"
                                src="blank.php"
                                style="width:100%; height:100%"
                                border="0"
                                frameborder="0"
                                allowfullscreen="true"
                                webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>
                        {% else %}
                            <iframe
                                id="content_id"
                                name="content_name"
                                src="{{ iframe_src }}"
                                style="width:100%; height:100%"
                                border="0"
                                frameborder="0"
                                allowfullscreen="true"
                                webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>
                        {% endif %}
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="lp-view-forum">
                    </div>
                </div>
            </div>
        </div>
    </div>
    {# end right Zone #}
</div>

<script>
    document.querySelector('.menu-button').onclick = function(e) {
        e.preventDefault(); document.querySelector('.circle').classList.toggle('open');
    }
    var LPViewUtils = {
        setHeightLPToc: function () {
            var scormInfoHeight = $('#scorm-info').outerHeight(true);
            $('#learning_path_toc').css({
                top: scormInfoHeight
            });
        }
    };

    $(function() {
        if (/iPhone|iPod|iPad/.test(navigator.userAgent)) {
            // Fix an issue where you cannot scroll below first screen in
            // learning paths on Apple devices
            document.getElementById('wrapper-iframe').setAttribute(
                'style',
                'width:100%; overflow:auto; position:auto; -webkit-overflow-scrolling:touch !important;'
            );
            // Fix another issue whereby buttons do not react to click below
            // second screen in learning paths on Apple devices
            document.getElementById('content_id').setAttribute('style', 'overflow: auto;');
        }

        {% if lp_mode == 'embedframe' %}
            $('#lp-view-expand-button, #lp-view-expand-toggle').on('click', function (e) {
                e.preventDefault();
                $('#learning_path_main').toggleClass('lp-view-collapsed');
                $('#lp-view-expand-toggle span.fa').toggleClass('fa-compress');
                $('#lp-view-expand-toggle span.fa').toggleClass('fa-expand');
                var className = $('#lp-view-expand-toggle span.fa').attr('class');
                if (className == 'fa fa-expand') {
                    $(this).attr('title', '{{ "Expand" | get_lang }}');
                } else {
                    $(this).attr('title', '{{ "Collapse" | get_lang }}');
                }

                if($('#navTabsbar').is(':hidden')) {
                    $('#navTabsbar').show();
                } else {
                    $('#navTabsbar').hide();
                }
            });
        {% else %}
            $('#lp-view-expand-button, #lp-view-expand-toggle').on('click', function (e) {
                e.preventDefault();
                $('#learning_path_main').toggleClass('lp-view-collapsed');
                $('#lp-view-expand-toggle span.fa').toggleClass('fa-expand');
                $('#lp-view-expand-toggle span.fa').toggleClass('fa-compress');

                var className = $('#lp-view-expand-toggle span.fa').attr('class');
                if (className == 'fa fa-expand') {
                    $(this).attr('title', '{{ "Expand" | get_lang }}');
                } else {
                    $(this).attr('title', '{{ "Collapse" | get_lang }}');
                }
            });
        {% endif %}

        $('.lp-view-tabs').on('click', '.disabled', function (e) {
            e.preventDefault();
        });

        $('a#ui-option').on('click', function (e) {
            e.preventDefault();
            var icon = $(this).children('.fa');
            if (icon.is('.fa-chevron-up')) {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');

                return;
            }
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        });

        LPViewUtils.setHeightLPToc();

        $('.image-avatar img').load(function () {
            LPViewUtils.setHeightLPToc();
        });

        $('.scorm_item_normal a, #scorm-previous, #scorm-next').on('click', function () {
            $('.lp-view-tabs').animate({opacity: 0}, 500);
        });

        $('#learning_path_right_zone #lp-view-content iframe').on('load', function () {
            $('.lp-view-tabs a[href="#lp-view-content"]').tab('show');
            $('.lp-view-tabs').animate({opacity: 1}, 500);
        });

        {% if lp_mode == 'embedded' %}
            $('.scorm_item_normal a, #scorm-previous, #scorm-next').on('click', function () {
                $('.lp-view-tabs').animate({opacity: 0}, 500);

                if ($('#flag-mobile').is(':visible') && !$('#learning_path_main').is('.lp-view-collapsed')) {
                    $('#lp-view-expand-toggle').trigger('click');
                }
            });
        {% endif %}

        loadForumThread({{ lp_id }}, {{ lp_current_item_id }});
        checkCurrentItemPosition({{ lp_current_item_id }});

        {% if glossary_extra_tools in glossary_tool_available_list %}
            // Loads the glossary library.
            (function () {
                {% if show_glossary_in_documents == 'ismanual' %}
                    $.frameReady(
                        function(){
                            //  $("<div>I am a div courses</div>").prependTo("body");
                        },
                        "#content_id",
                        [
                            { type:"script", id:"_fr1", src:"{{ jquery_web_path }}", deps: [
                                { type:"script", id:"_fr4", src:"{{ jquery_ui_js_web_path }}"},
                                { type:"script", id:"_fr2", src:"{{ _p.web_lib }}javascript/jquery.highlight.js"},
                                {{ fix_link }}
                            ]},
                            { type:"stylesheet", id:"_fr5", src:"{{ jquery_ui_css_web_path }}"},
                        ]
                    );
                {% elseif show_glossary_in_documents == 'isautomatic' %}
                    $.frameReady(
                        function(){
                            //  $("<div>I am a div courses</div>").prependTo("body");
                        },
                        "#content_id",
                        [
                            { type:"script", id:"_fr1", src:"{{ jquery_web_path }}", deps: [
                                { type:"script", id:"_fr4", src:"{{ jquery_ui_js_web_path }}"},
                                { type:"script", id:"_fr2", src:"{{ _p.web_lib }}javascript/jquery.highlight.js"},
                                {{ fix_link }}
                            ]},
                            { type:"stylesheet", id:"_fr5", src:"{{ jquery_ui_css_web_path }}"},
                        ]
                    );
                {% elseif fix_link != '' %}
                    $.frameReady(
                        function(){
                            //  $("<div>I am a div courses</div>").prependTo("body");
                        },
                        "#content_id",
                        [
                            { type:"script", id:"_fr1", src:"{{ jquery_web_path }}", deps: [
                                { type:"script", id:"_fr4", src:"{{ jquery_ui_js_web_path }}"},
                                {{ fix_link }}
                            ]},
                            { type:"stylesheet", id:"_fr5", src:"{{ jquery_ui_css_web_path }}"},
                        ]
                    );
                {% endif %}
            })();
        {% endif %}
        {% if disable_js_in_lp_view == 0 %}
            $(function() {
                var arr = ['link', 'sco'];
                if ($.inArray(olms.lms_item_type, arr) == -1) {
                    {{ frame_ready }}
                }
            });
        {% endif %}

        $(window).on('resize', function () {
            LPViewUtils.setHeightLPToc();
        });
    });
</script>
