{% if instant_meeting_form %}
    {{ instant_meeting_form }}
{% endif %}

{% if group_form %}
    {{ group_form }}
{% endif %}

{% if schedule_meeting_form %}
    {{ schedule_meeting_form }}
{% endif %}

{% if meetings.count %}
    <div class="page-header">
        <h2>{{ 'ScheduledMeetings'|get_lang }}</h2>
    </div>
    <style>
        td { font-size: 12px; }
    </style>
    <table class="table">
        <tr>
            <th>&nbsp;</th>
            <th>{{ 'Topic'|get_plugin_lang('ZoomPlugin') }}</th>
            <th>{{ 'Agenda'|get_plugin_lang('ZoomPlugin') }}</th>
            <th>{{ 'StartTime'|get_lang }}</th>
            <th>{{ 'Duration'|get_lang }}</th>
            <th>{{ 'Actions'|get_lang }}</th>
        </tr>
        {% for meeting in meetings %}
        <tr>
            <td>
                <input type="checkbox" class="chk-meeting-item" name="slt-meeting" value="{{ meeting.meetingId }}" />
            </td>
            <td>
                {{ meeting.meetingInfoGet.topic }}
            </td>
            <td>
                {{ meeting.meetingInfoGet.agenda|nl2br }}
            </td>
            <td>{{ meeting.formattedStartTime }}</td>
            <td>{{ meeting.formattedDuration }}</td>
            <td>
                <a class="btn btn-primary btn-xs" href="join_meeting.php?meetingId={{ meeting.meetingId }}&{{ _p.web_cid_query }}">
                    {{ 'Join'|get_plugin_lang('ZoomPlugin') }}
                </a>

                {% if is_manager %}
                    <a class="btn btn-default btn-xs" href="meeting.php?meetingId={{ meeting.meetingId }}&{{ _p.web_cid_query }}">
                        {{ 'Details'|get_plugin_lang('ZoomPlugin') }}
                    </a>

                    <a class="btn btn-danger btn-xs"
                       href="start.php?action=delete&meetingId={{ meeting.meetingId }}&{{ _p.web_cid_query }}"
                       onclick="javascript:if(!confirm('{{ 'AreYouSureToDeleteSelected' | get_plugin_lang('ZoomPlugin') }}')) return false;"
                    >
                        {{ 'Delete'|get_lang }}
                    </a>
                {% endif %}
            </td>
        </tr>
        {% endfor %}
    </table>
    <div>
        <a href="delete-meeting.php" class="btn btn-danger" id="delete-selected">
            {{ 'DeleteSelected'| get_plugin_lang('ZoomPlugin') }}
        </a>
    </div>
{% endif %}
