/**
 * Phire CMS 2.0 Scripts
 */

var phire = {
    clear             : null,
    curForm           : null,
    timeout           : null,
    submitted         : false,
    resourceCount     : 1,
    modelCount        : 1,
    valCount          : 1,
    curErrors         : 0,
    curValue          : null,
    selIds            : [],
    appUri            : null,
    appPath           : null,
    basePath          : null,
    contentPath       : null,
    sysBasePath       : null,
    i18n              : null,
    serverTzOffset    : 0,
    errorDisplay      : {
        "color"    : '#f00',
        "bgColor"  : '#ffe5e5',
        "orgColor" : '#fff',
        "speed"    : 500,
        "tween"    : 25,
        "easing"   : jax.tween.easein.quad
    },
    updateTitle : function(title, input) {
        jax(title).val(jax(title).data('title') + jax(input).val());
    },
    addResource       : function() {
        phire.resourceCount++;

        // Add resource select field
        jax('#resource_new_1').clone({
            "name" : 'resource_new_' + phire.resourceCount,
            "id"   : 'resource_new_' + phire.resourceCount
        }).appendTo(jax('#resource_new_1').parent());

        // Add permission select field
        jax('#permission_new_1').clone({
            "name" : 'permission_new_' + phire.resourceCount,
            "id"   : 'permission_new_' + phire.resourceCount
        }).appendTo(jax('#permission_new_1').parent());

        // Add type select field
        jax('#type_new_1').clone({
            "name" : 'type_new_' + phire.resourceCount,
            "id"   : 'type_new_' + phire.resourceCount
        }).appendTo(jax('#type_new_1').parent());

        // Add allow select field
        jax('#allow_new_1').clone({
            "name" : 'allow_new_' + phire.resourceCount,
            "id"   : 'allow_new_' + phire.resourceCount
        }).appendTo(jax('#allow_new_1').parent());

        jax('#resource_new_' + phire.resourceCount).val(jax('#resource_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#permission_new_' + phire.resourceCount).val(jax('#permission_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#type_new_' + phire.resourceCount).val(jax('#type_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        jax('#allow_new_' + phire.resourceCount).val(jax('#allow_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
    },
    changePermissions : function(sel) {
        var cur = (sel.id.indexOf('cur_') != -1) ? 'cur' : 'new';
        var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var marked = jax('#' + sel.id + ' > option:selected').val();

        var opts = jax('#permission_' + cur + '_' + id + ' > option').toArray();
        var start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        opts = jax('#type_' + cur + '_' + id + ' > option').toArray();
        start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        jax('#type_' + cur + '_' + id).append('option', {"value" : 0}, '(' + phire.i18n.t('All') + ')');
        jax('#permission_' + cur + '_' + id).append('option', {"value" : 0}, '(' + phire.i18n.t('All') + ')');

        if (marked != 0) {
            var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
            var j = jax.json.parse(jsonLoc + encodeURIComponent(marked.replace(/\\/g, '_')));
            for (type in j.types) {
                if (type != 0) {
                    jax('#type_' + cur + '_' + id).append('option', {"value" : type}, j.types[type]);
                }
            }
            for (var i = 0; i < j.actions.length; i++) {
                jax('#permission_' + cur + '_' + id).append('option', {"value" : j.actions[i]}, j.actions[i]);
            }
        }
    },
    customDatetime : function(val) {
        var j = jax.json.parse('./config/json/' + encodeURIComponent(val.replace(/\//g, '_')));
        if ((jax('#custom-datetime')[0] != undefined) && (j != undefined)) {
            var v = (j.format != undefined) ? '(' + j.format + ')' : '';
            jax('#custom-datetime').val(v);
        }
    },
    processForm : function(response) {
        var j = jax.json.parse(response.text);
        if (j.updated != undefined) {
            if (j.redirect != undefined) {
                window.location.href = j.redirect;
            } else {
                // If there is a history field
                if ((j.form != undefined) && (jax('#' + j.form)[0] != undefined)) {
                    var frm = jax('#' + j.form)[0];
                    if (frm.elements.length > 0) {
                        for (var name in frm.elements) {
                            if (name.indexOf('history_') != -1) {
                                var ids = name.split('_');
                                if (ids.length == 3) {
                                    if (jax('#field_' + ids[2])[0] != undefined) {
                                        phire.curValue = jax('#field_' + ids[2]).val();
                                    }
                                    var h = jax.json.parse(phire.basePath + phire.appUri + '/structure/fields/json/history/' + ids[1] + '/' + ids[2]);
                                    var hisSelOptions = jax('#' + name + ' > option');
                                    var start = hisSelOptions.length - 1;
                                    for (var i = start; i >= 0; i--) {
                                        jax(hisSelOptions[i]).remove();
                                    }
                                    jax('#' + name).append('option', {"value" : '0'}, '(' + phire.i18n.t('Current') + ')');
                                    for (var i = 0; i < h.length; i++) {
                                        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                        var dte     = new Date(h[i] * 1000);
                                        var month   = months[dte.getMonth()];
                                        var day     = dte.getDate();
                                        var year    = dte.getFullYear();
                                        var hours   = dte.getHours();
                                        var minutes = dte.getMinutes();
                                        var seconds = dte.getSeconds();

                                        if (day < 10) {
                                            day = '0' + day;
                                        }
                                        if (hours < 10) {
                                            hours = '0' + hours;
                                        }
                                        if (minutes < 10) {
                                            minutes = '0' + minutes;
                                        }
                                        if (seconds < 10) {
                                            seconds = '0' + seconds;
                                        }
                                        var dateFormat = month + ' ' + day + ', ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                                        jax('#' + name).append('option', {"value" : h[i]}, dateFormat);
                                    }
                                }
                            }
                        }
                    }
                }

                if (jax('#result')[0] != undefined) {
                    jax('#result').css({
                        "background-color" : '#dbf2bf',
                        "color"            : '#315900',
                        "opacity"          : 0
                    });
                    jax('#result').val(phire.i18n.t('Saved') + '!');
                    for (var i = 1; i <= phire.curErrors; i++) {
                        if (jax('#error-' + i)[0] != undefined) {
                            jax('#error-' + i).remove();
                        }
                    }
                    if (jax('#updated')[0] != undefined) {
                        jax('#updated').val(j.updated);
                    }
                    if ((j.form != undefined) && (jax('#' + j.form)[0] != undefined)) {
                        var f = jax('#' + j.form)[0];
                        for (var i = 0; i < f.elements.length; i++) {
                            if ((f.elements[i].type == 'text') || (f.elements[i].type == 'textarea')) {
                                f.elements[i].defaultValue = f.elements[i].value;
                            }
                        }
                        if (typeof CKEDITOR !== 'undefined') {
                            for (ed in CKEDITOR.instances) {
                                CKEDITOR.instances[ed].setData(f.elements[ed].value);
                            }
                        } else if (typeof tinymce !== 'undefined') {
                            for (ed in tinymce.editors) {
                                if (ed.indexOf('field_') != -1) {
                                    tinymce.editors[ed].setContent(f.elements[ed].value);
                                }
                            }
                        }
                    }
                    jax('#result').fade(100, {tween : 10, speed: 200});
                    phire.clear = setTimeout(phire.clearStatus, 3000);
                }
            }
        } else {
            if (jax('#result')[0] != undefined) {
                jax('#result').css({
                    "background-color" : '#e8d0d0',
                    "color"            : '#8e0202',
                    "opacity"          : 0
                });
                jax('#result').val(phire.i18n.t('Please correct the errors below.'));
                for (var i = 1; i <= phire.curErrors; i++) {
                    if (jax('#error-' + i)[0] != undefined) {
                        jax('#error-' + i).remove();
                    }
                }
                jax('#result').fade(100, {tween : 10, speed: 200});
                phire.clear = setTimeout(phire.clearStatus, 3000);
            }
            for (name in j) {
                // Check if the error already exists via a PHP POST
                var curErrorDivs = jax('#' + name).parent().getElementsByTagName('div');
                var curErrorDivsHtml = [];
                for (var i = 0; i < curErrorDivs.length; i++) {
                    curErrorDivsHtml.push(curErrorDivs[i].innerHTML);
                }
                // If error doesn't exists yet, append it
                if (curErrorDivsHtml.indexOf(j[name].toString()) == -1) {
                    phire.curErrors++;
                    jax(jax('#' + name).parent()).append('div', {"id" : 'error-' + phire.curErrors, "class" : 'error'}, j[name]);
                }

            }
        }
    },
    updateForm : function(form, ret) {
        phire.submitted = true;
        if (ret) {
            if (jax('#update_value')[0] != undefined) {
                jax('#update_value').val(1);
            }
            return true;
        } else {
            var f = jax(form)[0];
            if (typeof CKEDITOR !== 'undefined') {
                for (ed in CKEDITOR.instances) {
                    f.elements[ed].value = CKEDITOR.instances[ed].getData();
                }
            } else if (typeof tinymce !== 'undefined') {
                for (ed in tinymce.editors) {
                    if (ed.indexOf('field_') != -1) {
                        f.elements[ed].value = tinymce.editors[ed].getContent();
                    }
                }
            }
            var act = jax(form).attrib('action');
            var url = act + ((act.indexOf('?') != -1) ? '&update=1' : '?update=1');
            jax.ajax(url, {status : {200 : phire.processForm}, method : 'post', data : f});
            return false;
        }
    },
    clearStatus : function() {
        jax('#result').fade(0, {tween : 10, speed: 200});
        clearTimeout(phire.clear);
    },
    wipeErrors : function(a, hgt) {
        if (jax('#dir-errors').height() > 50) {
            jax(a).val(phire.i18n.t('Show'));
            jax('#dir-errors').wipeUp(17, {tween : 10, speed: 200});
        } else {
            jax(a).val(phire.i18n.t('Hide'));
            jax('#dir-errors').wipeUp(hgt, {tween : 10, speed: 200});
        }
    },
    showLoading : function() {
        document.getElementById('loading').style.display = 'block';
    },
    checkFormChange : function() {
        if (!phire.submitted) {
            var change = false;
            var f = jax(phire.curForm)[0];
            for (var i = 0; i < f.elements.length; i++) {
                if ((f.elements[i].type == 'text') || (f.elements[i].type == 'textarea')) {
                    if (f.elements[i].value != f.elements[i].defaultValue) {
                        change = true;
                    }
                }
            }
            if (typeof CKEDITOR !== 'undefined') {
                for (ed in CKEDITOR.instances) {
                    if (CKEDITOR.instances[ed].getData() != f.elements[ed].defaultValue) {
                        change = true;
                    }
                }
            } else if (typeof tinymce !== 'undefined') {
                for (ed in tinymce.editors) {
                    if (ed.indexOf('field_') != -1) {
                        if (tinymce.editors[ed].getContent() != f.elements[ed].defaultValue) {
                            change = true;
                        }
                    }
                }
            }
            if (change) {
                return phire.i18n.t('You are about to leave this page and have unsaved changes. Are you sure?');
            } else {
                return;
            }
        } else {
            return;
        }
    },
    addValidator : function() {
        phire.valCount++;

        // Add validator select field
        jax('#validator_new_1').clone({
            "name" : 'validator_new_' + phire.valCount,
            "id"   : 'validator_new_' + phire.valCount
        }).appendTo(jax('#validator_new_1').parent());

        // Add validator value text field
        jax('#validator_value_new_1').clone({
            "name" : 'validator_value_new_' + phire.valCount,
            "id"   : 'validator_value_new_' + phire.valCount
        }).appendTo(jax('#validator_value_new_1').parent());

        // Add validator message text field
        jax('#validator_message_new_1').clone({
            "name" : 'validator_message_new_' + phire.valCount,
            "id"   : 'validator_message_new_' + phire.valCount
        }).appendTo(jax('#validator_message_new_1').parent());
    },
    addModel : function() {
        var parentModelId = phire.modelCount;
        phire.modelCount++;

        // Add model select field
        jax('#model_new_' + parentModelId).clone({
            "name" : 'model_new_' + phire.modelCount,
            "id"   : 'model_new_' + phire.modelCount
        }).appendTo(jax('#model_new_' + parentModelId).parent());

        // Add type_id text field
        jax('#type_id_new_' + parentModelId).clone({
            "name"  : 'type_id_new_' + phire.modelCount,
            "id"    : 'type_id_new_' + phire.modelCount,
            "value" : 0
        }).appendTo(jax('#type_id_new_' + parentModelId).parent());

        // Select marked clean up
        var sel1 = jax('#model_new_' + parentModelId)[0];
        var sel2 = jax('#model_new_' + phire.modelCount)[0];
        var marked1 = null;
        var marked2 = null;

        for (var i = 0; i < sel1.options.length; i++) {
            if (sel1.options[i].selected) {
                marked1 = i;
            }
            if (sel2.options[i].selected) {
                marked2 = i;
                sel2.options[i].selected = false;
            }
        }

        if (marked1 != marked2) {
            sel2.options[marked1].selected = true;
        }
    },
    addFields : function(flds) {
        var fieldCount = 1;

        // Get the next field number
        while (jax('#field_' + flds[0] + '_new_' + fieldCount)[0] != undefined) {
            fieldCount++;
        }

        // Clone the fields
        for (var i = 0; i < flds.length; i++) {
            var oldName = 'field_' + flds[i] + '_new_1';
            var newName = 'field_' + flds[i] + '_new_' + fieldCount;
            var oldObj = jax('#' + oldName)[0];

            // If the object is a checkbox or radio set, clone the fieldset
            if ((oldObj.type == 'checkbox') || (oldObj.type == 'radio')) {
                var fldSet = jax(oldObj).parent();
                var fldSetInputs = fldSet.getElementsByTagName('input');
                var vals = [];
                var mrk = [];
                for (var j = 0; j < fldSetInputs.length; j++) {
                    vals.push(fldSetInputs[j].value);
                    if (fldSetInputs[j].checked) {
                        mrk.push(fldSetInputs[j].value);
                    }
                }
                var fldSetParent = jax(fldSet).parent();
                if (oldObj.type == 'checkbox') {
                    var attribs = {"name" : newName + '[]', "id" : newName};
                    jax(fldSetParent).appendCheckbox(vals, attribs, mrk);
                } else {
                    var attribs = {"name" : newName, "id" : newName};
                    jax(fldSetParent).appendRadio(vals, attribs, mrk);
                }
                // Else, clone the input or select
            } else {
                var realNewName = ((oldObj.nodeName == 'SELECT') && (oldObj.getAttribute('multiple') != undefined)) ?
                    newName + '[]' :
                    newName;
                jax('#' + oldName).clone({
                    "name" : realNewName,
                    "id"   : newName
                }).appendTo(jax('#' + oldName).parent());

                if (jax('#' + newName)[0].value != '') {
                    jax('#' + newName)[0].value = '';
                }
            }
        }
    },
    changeModelTypes : function(sel) {
        var id = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var cur = (sel.id.indexOf('new_') != -1) ? 'new_' : 'cur_';
        var marked = jax('#' + sel.id + ' > option:selected').val();
        var opts = jax('#type_id_' + cur + id + ' > option').toArray();
        var start = opts.length - 1;

        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }

        // Get new model types and create new select drop down
        var jsonLoc = (window.location.href.indexOf('edit') != -1) ? '../json/' : './json/';
        var j = jax.json.parse(jsonLoc + marked.replace(/\\/g, '_'));
        if (j.types[0] != undefined) {
            var types = [];
            for (key in j.types) {
                types.push([key, j.types[key]]);
            }
        }
        for (var i = 0; i < types.length; i++) {
            jax('#type_id_' + cur + id).append('option', {"value" : types[i][0]}, types[i][1]);
        }
    },
    changeHistory : function(sel, basePath) {
        var ids = sel.id.substring(sel.id.indexOf('_') + 1).split('_');
        var modelId = ids[0];
        var fieldId = ids[1];
        var marked = jax('#' + sel.id + ' > option:selected').val();

        if ((phire.curValue == null) && (jax('#field_' + fieldId)[0] != undefined)) {
            phire.curValue = jax('#field_' + fieldId).val();
        }

        if (marked != 0) {
            var j = jax.json.parse(basePath + '/structure/fields/json/history/' + modelId + '/' + fieldId + '/' + marked);
            if (jax('#field_' + j.fieldId)[0] != undefined) {
                if (typeof CKEDITOR !== 'undefined') {
                    if (CKEDITOR.instances['field_' + j.fieldId] != undefined) {
                        CKEDITOR.instances['field_' + j.fieldId].setData(j.value);
                    }
                } else if (typeof tinymce !== 'undefined') {
                    tinymce.activeEditor.setContent(j.value);
                }
                jax('#field_' + j.fieldId).val(j.value);
            }
        } else {
            if (jax('#field_' + fieldId)[0] != undefined) {
                if (typeof CKEDITOR !== 'undefined') {
                    if (CKEDITOR.instances['field_' + fieldId] != undefined) {
                        CKEDITOR.instances['field_' + fieldId].setData(phire.curValue);
                    }
                } else if (typeof tinymce !== 'undefined') {
                    tinymce.activeEditor.setContent(phire.curValue);
                }
                jax('#field_' + fieldId).val(phire.curValue);
            }
        }
    },
    changeEditor : function(a) {
        var content = '';
        var val     = jax(a).data('editor');
        var status  = jax(a).data('editor-status');
        var id      = a.id.substring(a.id.indexOf('-') + 1);
        var w       = Math.round(jax('#field_' + id).width());
        var h       = Math.round(jax('#field_' + id).height());

        if (status == 'on') {
            jax(a).val(phire.i18n.t('Editor'));
            jax(a).data('editor-status', 'off');
            if (typeof CKEDITOR !== 'undefined') {
                content = CKEDITOR.instances['field_' + id].getData();
                CKEDITOR.instances['field_' + id].destroy();
            } else if (typeof tinymce !== 'undefined') {
                content = tinymce.activeEditor.getContent();
                tinymce.get('field_' + id).hide();
            }
            jax('#field_' + id).val(content);
            jax('#field_' + id).show();
        } else {
            jax(a).val(phire.i18n.t('Source'));
            jax(a).data('editor-status', 'on');
            if (val == 'ckeditor') {
                phire.loadEditor('ckeditor', id);
            } else if (val == 'tinymce') {
                phire.loadEditor('tinymce', id);
            }
        }
    },
    toggleEditor : function(sel) {
        if (jax(sel).val().indexOf('textarea') != -1) {
            jax('#editor').show();
        } else {
            jax('#editor').hide();
        }
    },
    loadEditor : function(editor, id) {
        if (null != id) {
            var w = Math.round(jax('#field_' + id).width());
            var h = Math.round(jax('#field_' + id).height());
            phire.selIds = [{ "id" : id, "width" : w, "height" : h }];
        }

        if (phire.selIds.length > 0) {
            for (var i = 0; i < phire.selIds.length; i++) {
                if (editor == 'ckeditor') {
                    if (CKEDITOR.instances['field_' + phire.selIds[i].id] == undefined) {
                        CKEDITOR.replace(
                            'field_' + phire.selIds[i].id,
                            {
                                width          : phire.selIds[i].width,
                                height         : phire.selIds[i].height,
                                allowedContent : true
                            }
                        );
                    }
                } else if (editor == 'tinymce') {
                    if (tinymce.editors['field_' + phire.selIds[i].id] == undefined) {
                        tinymce.init(
                            {
                                selector              : "textarea#field_" + phire.selIds[i].id,
                                theme                 : "modern",
                                plugins: [
                                    "advlist autolink lists link image hr", "searchreplace wordcount code fullscreen",
                                    "table", "template paste textcolor"
                                ],
                                image_advtab          : true,
                                toolbar1              : "insertfile undo redo | styleselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link image",
                                width                 : phire.selIds[i].width,
                                height                : phire.selIds[i].height,
                                relative_urls         : false,
                                convert_urls          : 0,
                                remove_script_host    : 0
                            }
                        );
                    } else {
                        tinymce.get('field_' + phire.selIds[i].id).show();
                    }
                }
            }
        }
    }
};

/**
 * Document ready function for Phire
 */
jax(document).ready(function(){
    phire.i18n = jax.i18n();
    if (jax.cookie.load('phire') != '') {
        var phireCookie = jax.cookie.load('phire');

        phire.appUri         = phireCookie.app_uri;
        phire.appPath        = phireCookie.app_path;
        phire.contentPath    = phireCookie.content_path;
        phire.basePath       = phireCookie.base_path;
        phire.sysBasePath    = phireCookie.base_path + phireCookie.app_uri;
        phire.serverTzOffset = phireCookie.server_tz_offset;

        phire.i18n.loadFile(phire.basePath + phire.contentPath + '/assets/phire/i18n/' + phire.i18n.getLanguage() + '.xml');

        if (phireCookie.modules.length > 0) {
            for (var i = 0; i < phireCookie.modules.length; i++) {
                if (phireCookie.modules[i].i18n) {
                    phire.i18n.loadFile(phire.basePath + phire.contentPath + '/assets/' + phireCookie.modules[i].name.toLowerCase() + '/i18n/' + phire.i18n.getLanguage() + '.xml');
                }
            }
        }
    }

    if (typeof _exp != 'undefined') {
        phire.timeout = setInterval(function() {
            if (jax('#logout-warning-back')[0] == undefined) {
                var url = decodeURIComponent(_base);
                jax('body').append('div', {id : 'logout-warning-back'});
                jax('body').append('div', {id : 'logout-warning'}, '<h3 style="margin: 15px 0 10px 0; font-size: bold;">' + phire.i18n.t('Your session is about to end.') + '</h3><h4 id="countdown">30</h4><a href="#" id="continue">' + phire.i18n.t('Continue') + '</a> <a href="' + url + '/logout" id="logout">' + phire.i18n.t('Logout') + '</a>');
                jax('#logout-warning-back').css({
                    "opacity" : 80,
                    "width"   : jax().width() + 'px',
                    "height"  : jax().getScrollHeight() + 'px',
                    "display" : 'block'
                });
                jax('#logout-warning').css({
                    "left" : Math.round((jax().width() / 2) - 170) + 'px'
                });

                var resizeLogout = function() {
                    jax('#logout-warning-back').css({
                        "width"   : jax().width() + 'px',
                        "height"  : jax().getScrollHeight() + 'px'
                    });
                    jax('#logout-warning').css({
                        "left" : Math.round((jax().width() / 2) - 170) + 'px'
                    });
                };

                jax().on('resize', resizeLogout);

                var countDown = setInterval(function(){
                    var sec = parseInt(jax('#countdown').val());
                    if (sec > 0) {
                        var newSec = sec - 1;
                        jax('#countdown').val(newSec);
                    } else {
                        window.location = url;
                    }
                }, 1000);

                jax('#continue').click(function(){
                    clearInterval(countDown);
                    jax().off('resize', resizeLogout);
                    jax('#logout-warning-back').remove();
                    jax('#logout-warning').remove();
                    jax.ajax(url + '/session');
                    return false;
                });
            }
        }, _exp * 1000);
    }

    // Check saved timestamp to determine if the saved div should display
    if ((jax.query('saved') != undefined) || (jax.query('removed') != undefined))  {
        if (jax.query('saved') != undefined) {
            var tValue  = jax.query('saved');
            var msg     = 'Saved';
            var fgColor   = '#315900';
            var bgColor = '#dbf2bf';
        } else {
            var tValue  = jax.query('removed');
            var msg     = 'Removed';
            var fgColor = '#8e0202';
            var bgColor = '#e8d0d0';
        }

        var tzOffset = Math.abs(new Date().getTimezoneOffset());
        var ts = Math.round(new Date().getTime() / 1000);
        var diff = Math.abs(Math.abs(tValue - ts) - (Math.abs(tzOffset - phire.serverTzOffset) * 60));
        if (diff < 30) {
            if (jax('#result')[0] != undefined) {
                jax('#result').css({
                    "background-color" : bgColor,
                    "color"            : fgColor,
                    "opacity"          : 0
                });
                jax('#result').val(phire.i18n.t(msg) + '!');
                jax('#result').fade(100, {tween : 10, speed: 200});
                phire.clear = setTimeout(phire.clearStatus, 3000);
            }
        }
    }

    if (jax('#errors')[0] != undefined) {
        jax('#errors').css('opacity', 100);
    }

    // For login form
    if (jax('#login-form')[0] != undefined) {
        var loginForm = jax('#login-form').form({
            "username" : {
                "required" : true
            },
            "password" : {
                "required" : true
            }
        });

        loginForm.setErrorDisplay(phire.errorDisplay);
        loginForm.submit(function(){
            return loginForm.validate();
        });
    }

    // For field form
    if (jax('#field-form')[0] != undefined) {
        var fieldForm = jax('#field-form').form({
            "name" : {
                "required" : true
            }
        });

        fieldForm.setErrorDisplay(phire.errorDisplay);
        fieldForm.submit(function(){
            return fieldForm.validate();
        });
    }

    // For field group form
    if (jax('#field-group-form')[0] != undefined) {
        var fieldGroupForm = jax('#field-group-form').form({
            "name" : {
                "required" : true
            }
        });

        fieldGroupForm.setErrorDisplay(phire.errorDisplay);
        fieldGroupForm.submit(function(){
            return fieldGroupForm.validate();
        });
    }

    // For user role form
    if (jax('#user-role-form')[0] != undefined) {
        var userRoleForm = jax('#user-role-form').form({
            "name" : {
                "required" : true
            }
        });

        userRoleForm.setErrorDisplay(phire.errorDisplay);
        userRoleForm.submit(function(){
            return userRoleForm.validate();
        });
    }

    // For user type form
    if (jax('#user-type-form')[0] != undefined) {
        var userTypeForm = jax('#user-type-form').form({
            "type" : {
                "required" : true
            }
        });

        userTypeForm.setErrorDisplay(phire.errorDisplay);
        userTypeForm.submit(function(){
            return userTypeForm.validate();
        });
    }

    if (jax('#model_1')[0] != undefined) {
        while (jax('#model_' + phire.modelCount)[0] != undefined) {
            phire.modelCount++;
        }
        phire.modelCount--;
    }
    if (jax('#field-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#field-remove-form').checkAll(this.value);
            } else {
                jax('#field-remove-form').uncheckAll(this.value);
            }
        });
        jax('#field-remove-form').submit(function(){
            return jax('#field-remove-form').checkValidate('checkbox', true);
        });
    }

    if (jax('#field-group-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#field-group-remove-form').checkAll(this.value);
            } else {
                jax('#field-group-remove-form').uncheckAll(this.value);
            }
        });
        jax('#field-group-remove-form').submit(function(){
            return jax('#field-group-remove-form').checkValidate('checkbox', true);
        });
    }

    var aLinks = jax('a.editor-link').toArray();
    var aVal = null;
    if ((aLinks != '') && (aLinks.length > 0)) {

        for (var i = 0; i < aLinks.length; i++) {
            if (aLinks[i].id.indexOf('editor-') != -1) {
                var id = aLinks[i].id.substring(aLinks[i].id.indexOf('-') + 1);
                var w = Math.round(jax('#field_' + id).width());
                var h = Math.round(jax('#field_' + id).height());
                phire.selIds.push({ "id" : id, "width" : w, "height" : h });
                aVal = jax(aLinks[i]).data('editor');
            }
        }

        if (null != aVal) {
            var head = document.getElementsByTagName('head')[0];
            var script = document.createElement("script");
            switch (aVal) {
                case 'ckeditor':
                    script.src = jax.root + 'ckeditor/ckeditor.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof CKEDITOR != 'undefined') {
                            phire.loadEditor('ckeditor');
                        }
                    }
                    head.appendChild(script);
                    break;

                case 'tinymce':
                    script.src = jax.root + 'tinymce/tinymce.min.js';
                    script.onload = script.onreadystatechange = function() {
                        if (typeof tinymce != 'undefined') {
                            phire.loadEditor('tinymce');
                        }
                    }
                    head.appendChild(script);
                    break;
            }
        }
    }

    if (jax('#themes-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#themes-remove-form').checkAll(this.value);
            } else {
                jax('#themes-remove-form').uncheckAll(this.value);
            }
        });
        jax('#themes-remove-form').submit(function(){
            return jax('#themes-remove-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#modules-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#modules-remove-form').checkAll(this.value);
            } else {
                jax('#modules-remove-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#user-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#user-remove-form').checkAll(this.value);
            } else {
                jax('#user-remove-form').uncheckAll(this.value);
            }
        });
        jax('#user-remove-form').submit(function(){
            return jax('#user-remove-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#role-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#role-remove-form').checkAll(this.value);
            } else {
                jax('#role-remove-form').uncheckAll(this.value);
            }
        });
        jax('#role-remove-form').submit(function(){
            return jax('#role-remove-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#session-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#session-remove-form').checkAll(this.value);
            } else {
                jax('#session-remove-form').uncheckAll(this.value);
            }
        });
        jax('#session-remove-form').submit(function(){
            return jax('#session-remove-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#type-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#type-remove-form').checkAll(this.value);
            } else {
                jax('#type-remove-form').uncheckAll(this.value);
            }
        });
        jax('#type-remove-form').submit(function(){
            return jax('#type-remove-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#sites-remove-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#sites-remove-form').checkAll(this.value);
            } else {
                jax('#sites-remove-form').uncheckAll(this.value);
            }
        });
        jax('#sites-remove-form').submit(function(){
            return jax('#sites-remove-form').checkValidate('checkbox', true);
        });
    }
});
