window.[module_name]Tool = {
    tableConfig : null,
    tempLoader    : "<div id=\"loader\" class=\"overlay-loader\"><img class=\"loader-icon spinning-cog\" src=\"/MelisCore/assets/images/cog12.svg\" data-cog=\"cog12\"></div>",
    currentRequest : null,
    refreshTable : function() {
        var targetTable = $("#[module_name]ToolTable");
        // append loader
        $("#[module_name]ToolTable_wrapper").append([module_name]Tool.tempLoader);
        targetTable.DataTable().ajax.reload(function(){
            $("#[module_name]ToolTable_wrapper").find("#loader").remove();
        });
    },
    getToolModal : function(callback, id){
        if (typeof(callback) ==='undefined') callback = null;
        if (typeof(id) ==='undefined') id = 0;
        var data = "";
        [module_name]Tool.currentRequest =  $.ajax({
            type: 'GET',
            url: '/melis/[module_name]/form/' + id,
            data : {
                id : id
            },
        }).done(function (returnData) {

            if(typeof callback !== "undefined" && typeof callback === "function") {
                callback(returnData);
            }

            data = returnData;
        });

        return data;
    },
    getAlbumById : function(id) {
        $.ajax({
            type: 'GET',
            url: '/melis/[module_name]/get-lumen-data/'+ id,
            dataType: 'json',
            encode: true
        }).done(function (data) {
            console.log(data);
        });
    },
    saveData : function(data,callback,callbackFail){
        if (typeof(callback) ==='undefined') callback = null;
        if (typeof(callbackFail) ==='undefined') callbackFail = null;

        $.ajax({
            type        : 'POST',
            url         : '/melis/[module_name]/save',
            data        : data,
            dataType    : 'json',
            encode		: true,
            contentType : false,
            processData : false,
            cache : false
        }).done(function(data){
            if(data.success) {
                if(typeof callback !== "undefined" && typeof callback === "function") {
                    callback(data);
                }
               melisHelper.melisOkNotification(data.textTitle, data.textMessage);
            }
            else
            {
                melisCoreTool.alertDanger("#prospectupdateformalert", '', data.textMessage);
                melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors, 0);
                melisCoreTool.highlightErrors(data.success, data.errors, "[form_name]");
                if(typeof callbackFail !== "undefined" && typeof callbackFail === "function") {
                    callbackFail(data);
                }
            }
            melisCore.flashMessenger();

        });
    },
    deleteAlbum : function(id, callback){

        if (typeof(callback) ==='undefined') callback = null;
        $.ajax({
            type        : 'POST',
            url         : '/melis/[module_name]/delete',
            data        : { id : id},
            dataType    : 'json',
            encode		: true
        }).done(function(data){
            if(data.success) {
                if(typeof callback !== "undefined" && typeof callback === "function") {
                    callback();
                }
                melisHelper.melisOkNotification(data.textTitle, data.textMessage);
            }
            else
            {
                melisCoreTool.alertDanger("#prospectupdateformalert", '', data.textMessage);
                melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors, 0);
                melisCoreTool.highlightErrors(data.success, data.errors, "idformprospectdata");
            }
            melisCore.flashMessenger();
            melisCoreTool.done("#btnProspectUpdate");
        });
    },

};
//if (typeof [module_name]Tool == undefined) {
    (function ($){
        [add-button-event]
        [edit-button-event]
        [save-button-event]
        /*
         * submit form
         */
        $("body").on('submit',"#[module_name]form",function(e){
            e.preventDefault();
            var saveBtn = $("#btn-save-lumen-album");
            saveBtn.attr('disabled','disabled');
            var formData = $(this).serializeArray();
            [module_name]Tool.saveAlbumData(formData,function(data){
                $(".lumen-modal-close").trigger('click');
                // reload the tool
                [module_name]Tool.refreshTable();
                [tab_save_callback]
            },function(){
                saveBtn.removeAttr('disabled')
            });
        });
        /*
         * delete an album
         */
        $(".btnDelLumenAlbum").click(function(){
            
        });
        $("body").on('click', ".delete-[module_name]", function(){
            var id = $(this).parent().parent().attr('id');
            melisCoreTool.confirm(
                translations.tr_meliscore_common_yes,
                translations.tr_meliscore_common_no,
                translations.tr_[module_name]_common_delete_item,
                translations.tr_[module_name]_common_delete_message,
                function () {
                    // append loader
                    [module_name]Tool.deleteAlbum(id,function(){
                        // refresh tool
                        [module_name]Tool.refreshTable();
                    });
                }
            );
        });
        /*
         * refresh tool
         */
        $("body").on('click',".melis-lumen-refresh",function(){
            [module_name]Tool.refreshTable();
        })
        /*
         * cancel ajax request when canceled
         */
        $("body").on('hidden.bs.modal',"#[module_name]Modal",function(){
            [module_name]Tool.currentRequest.abort();
        });

    })(jQuery);
//}