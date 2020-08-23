/**
 * Created by SS on 23.08.2019.
 */
$(document).ready(function() {

    $(document).on('click', '.modalButton', function (e) {
        e.preventDefault();

        var container = $('#modalContent');
        var header = $('#modalHeader');
        // Очищаем контейнер
        container.html('Пожалуйста, подождите. Идет загрузка...');
        // Выводим модальное окно, загружаем данные
        $("#modal").data('bs.modal')._config.backdrop = 'static';
        $("#modal").data('bs.modal')._config.keyboard = false;
        $('#modal').find(header).text($(this).attr('title'));
        $('#modal').modal('show').find(container).load($(this).attr('value'));
        $('#modal').on('shown.bs.modal', function () {
            var idArr = [];
            $('#modal').find('input,select').each(function() {
                if (this.id!=''){
                    idArr[idArr.length]= this.id;
                }
            });
            // console.log(idArr);
            $("#"+idArr[0]).focus();
            idArr = [];
        });
        $("#modal").on('hidden.bs.modal', function () {
            $('#modalContent').html('');
        });
    });

    function dropDownFixPosition(button, dropdown) {
        var dropDownTop = button.offset().top + button.outerHeight();
        var left = button.offset().left - dropdown.width() + button.parent().width();
        dropdown.css('top', dropDownTop + "px");
        dropdown.css('left', left + "px");
        dropdown.css('position', "absolute");

        // dropdown.css('width', dropdown.width());
        // dropdown.css('heigt', dropdown.height());
        dropdown.css('display', 'block');
        dropdown.appendTo('body');
    }

    function returnDropdownToParent(button, dropdown) {
        dropdown.hide();
        dropdown.insertAfter(button);
    }

    var openedUl = null;
    // $('.table-responsive').on('show.bs.dropdown', function (e) {
    $(document).on('show.bs.dropdown', '.table-responsive', function (e) {
        var buttonGroup = e.relatedTarget;
        var ul = $(buttonGroup).siblings('ul');
        openedUl = ul;
        dropDownFixPosition($(buttonGroup), $(ul));
    });

    // $('.table-responsive').on('hide.bs.dropdown', function (e) {
    $(document).on('hide.bs.dropdown', '.table-responsive', function (e) {
        returnDropdownToParent($(e.relatedTarget), openedUl);
    });

    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $.ajax({
                url: '/site/class-menu'
            });
            $('#sidebar').toggleClass('active');
        });
    });

});
