<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{{ 'ItemsConfig'|get_plugin_lang('BuyCoursesPlugin') }}</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                {{ items_form }}
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $("a[name='add']").click(function () {
            var selectedFrequency = $("#frequency_value").val();
            var selectedFrecuencyPrice = $("#frequency_price").val();

            if (selectedFrecuencyPrice === "0") {
                return;
            }

            var inputs = $("tbody tr td .frecuency-days");

            for (var i = 0; i < inputs.length; i++){
                if (inputs[i].value === selectedFrequency) {
                    return;
                }
            }

            var count = $("tbody tr").length;
            var frecuencyRow = '<tr><td><input class=\"frecuency-days\" type="hidden" name=\"frecuencies['+ (count + 1) + '][days]\" value="'+selectedFrequency+'" />' + selectedFrequency + '</input></td><td><input type="hidden" name=\"frecuencies['+ (count + 1) + '][price]\" value="'+selectedFrecuencyPrice+'" />' + selectedFrecuencyPrice + '</td><td><a name=\"delete\" class=\"btn btn-danger btn-sm\"><em class=\"fa fa-remove\"></em></a></td></tr>';

            $("tbody").append(frecuencyRow);
        });

        $("tbody").on("click", "tr td a", function(){
            var elementToDelete = $(this).closest("tr");
            elementToDelete.remove();
        });
    });
</script>

