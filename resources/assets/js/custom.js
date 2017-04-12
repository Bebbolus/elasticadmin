$("#menu-toggle").click(function(e) {
    e.preventDefault();
    $("#wrapper").toggleClass("toggled");
});

$(document).ready(function () {
    $("#radio_type").on('click',function(e) {
        // e.preventDefault();

        checkOtherOption();
    });

    $(".checkbox").on('click',function(e) {
        // e.preventDefault();

        checkRadioOption();
    });

    //Initialize tooltips
    $('.nav-tabs > li a[title]').tooltip();

    //Wizard
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

        var $target = $(e.target);

        if ($target.parent().hasClass('disabled')) {
            return false;
        }
    });

    $(".next-step").click(function (e) {

        var $active = $('.wizard .nav-tabs li.active');
        $active.next().removeClass('disabled');
        nextTab($active);

    });
    $(".prev-step").click(function (e) {

        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);

    });

    $("#select_index_input").on("change ready", function (e) {
        changeIndexValue(this.value,$("#select_type_input").val() );
    });


    $("#select_type_input").change(function (e) {
        showTypeMeta( $("#select_index_input").val() ,this.value);
    });

});

function nextTab(elem) {
    $(elem).next().find('a[data-toggle="tab"]').click();
}
function prevTab(elem) {
    $(elem).prev().find('a[data-toggle="tab"]').click();
}

function checkOtherOption(){
    var elements = document.getElementsByClassName("checkbox");
    for(var i = 0; i < elements.length; i++) {
        elements[i].checked = false;
    }
    // alert(elements);
}

function checkRadioOption(){
    $("#radio_type").prop('checked',false);
    // alert(elements);
}
function showTypeIndex(indexName)
{
    $('#search_meta').html('');

    data = indices[indexName]['mappings'];
    var items = [];
    items.push('<option value="" selected></option>');
    $.each(data , function(obj){
        if(obj != '_DEFAULT_') {
            items.push('<option value="' + obj + '">' + obj.toUpperCase() + '</option>');
        }
    });
    $('#select_type_input').html(items.join('') + '');
    //TODO: levare le precedenti option dalla select


}

function showTypeMeta(indexName, typeName)
{

    data = indices[indexName]['mappings'][typeName]['properties'];

    var items = [];
    $.each(data, function(obj) {
        if(obj != 'id') {
            items.push('<div class="col-md-4 col-xs-12"> <div class="form-control-wrapper"><div class="form-label"> ' + obj + ' </div><div class="input-group"><input name="metadata[' + obj + ']" type="text" class="form-control input"  value=""></div></div> </div>');
        }
     });
    $('#search_meta').html('<hr><div class="col-md-12 col-xs-12 text-center"></div>' + items.join('') + '');

}


function changeIndexValue(index, type){
    showTypeIndex(index);
    showTypeMeta( index ,type);
}

