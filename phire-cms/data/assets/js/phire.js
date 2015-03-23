/**
 * Phire CMS 2 Scripts
 */

var phire = {
    clear         : null,
    resourceCount : 1,
    currentForm   : null,
    submitted     : false,

    createSlug : function(text, field) {
        var text = new jax.String(text);
        jax(field).val(text.slug());
        return false;
    },

    addResource : function(vals) {
        if (vals == null) {
            vals = [{
                "resource"   : '',
                "action"     : '',
                "permission" : ''
            }];
        }
        for (var i = 0; i < vals.length; i++) {
            phire.resourceCount++;

            // Add resource select field
            jax('#resource_1').clone({
                "name": 'resource_' + phire.resourceCount,
                "id": 'resource_' + phire.resourceCount
            }).appendTo(jax('#resource_1').parent());

            if ((vals[i].resource != '') && (vals[i].resource != null)) {
                jax('#resource_' + phire.resourceCount).val(vals[i].resource);
            } else {
                jax('#resource_' + phire.resourceCount).val(jax('#resource_' + (phire.resourceCount - 1) + ' > option:selected').val());
            }

            // Add action select field
            jax('#action_1').clone({
                "name": 'action_' + phire.resourceCount,
                "id": 'action_' + phire.resourceCount
            }).appendTo(jax('#action_1').parent());

            // Add permission select field
            jax('#permission_1').clone({
                "name": 'permission_' + phire.resourceCount,
                "id": 'permission_' + phire.resourceCount
            }).appendTo(jax('#permission_1').parent());

            if ((vals[i].permission != '') &&(vals[i].permission != null)) {
                jax('#permission_' + phire.resourceCount).val(((vals[i].permission == 'allow') ? 1 : 0));
            }

            phire.changeActions(jax('#resource_' + phire.resourceCount)[0]);

            if ((vals[i].action != '') && (vals[i].action != null)) {
                jax('#action_' + phire.resourceCount).val(vals[i].action);
            }
        }
        return false;
    },

    changeActions : function(sel) {
        var id    = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var opts  = jax('#action_' + id + ' > option').toArray();
        var start = opts.length - 1;
        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }
        jax('#action_' + id).append('option', {"value" : '----'}, '----');

        if ((jax.cookie.load('phire') != '') && (jax(sel).val() != '----')) {
            var phireCookie = jax.cookie.load('phire');
            var json = jax.get(phireCookie.base_path + phireCookie.app_uri + '/roles/json/' + jax(sel).val());
            if (json.permissions != undefined) {
                for (var i = 0; i < json.permissions.length; i++) {
                    jax('#action_' + id).append('option', {"value" : json.permissions[i]}, json.permissions[i]);
                }
            }
        }

        return false;
    },

    selectUserRole : function(sel, path) {
        var id = jax(sel).val();
        if (id != '0') {
            window.location.href = path + '/users/add/' + id;
        }
    },

    changeTitle : function(value) {
        if (jax('#title-span')[0] != undefined) {
            jax('#title-span').val(value);
        }
    },

    customDatetime : function(path) {
        if ((jax('#datetime_format8')[0].checked) && (jax('#datetime_format_custom').val() != '')) {
            var val  = jax('#datetime_format_custom').val();
            var json = jax.get(path + '/config/json/' + encodeURIComponent(val.replace(/\//g, '_')));
            if ((jax('#datetime-custom')[0] != undefined) && (json != undefined)) {
                var v = (json.format != undefined) ? '(' + json.format + ')' : '';
                jax('#datetime-custom').val(v);
            }
        } else if (jax('#datetime-custom')[0] != undefined) {
            jax('#datetime-custom').val('');
        }
    },

    clearStatus : function(id) {
        jax(id).fade(0, {tween : 10, speed: 200});
        clearTimeout(phire.clear);
    },

    checkFormChange : function() {
        if (!phire.submitted) {
            var change = false;
            var form   = jax(phire.currentForm)[0];
            for (var i = 0; i < form.elements.length; i++) {
                if ((form.elements[i].type == 'text') || (form.elements[i].type == 'textarea')) {
                    if (form.elements[i].value != form.elements[i].defaultValue) {
                        change = true;
                    }
                }
            }
            if (change) {
                return 'You are about to leave this page and have unsaved changes. Are you sure?';
            } else {
                return;
            }
        }
    }

};

jax(document).ready(function(){
    if (jax.query('installed') != undefined) {
        if (jax('#installed')[0] != undefined) {
            jax('#installed').css({"opacity" : 0});
            jax('#installed').fade(100, {tween : 10, speed: 200});
            phire.clear = setTimeout(function(){
                phire.clearStatus('#installed');
            }, 3000);
        }
    }
    if (jax.query('saved') != undefined) {
        if (jax('#saved')[0] != undefined) {
            jax('#saved').css({"opacity" : 0});
            jax('#saved').fade(100, {tween : 10, speed: 200});
            phire.clear = setTimeout(function(){
                phire.clearStatus('#saved');
            }, 2500);
        }
    }
    if (jax.query('removed') != undefined) {
        if (jax('#removed')[0] != undefined) {
            jax('#removed').css({"opacity" : 0});
            jax('#removed').fade(100, {tween : 10, speed: 200});
            phire.clear = setTimeout(function(){
                phire.clearStatus('#removed');
            }, 2500);
        }
    }
    if (jax('#modules-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#modules-form').checkAll(this.value);
            } else {
                jax('#modules-form').uncheckAll(this.value);
            }
        });
    }
    if (jax('#users-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#users-form').checkAll(this.value);
            } else {
                jax('#users-form').uncheckAll(this.value);
            }
        });
        jax('#users-form').submit(function(){
            return jax('#users-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#roles-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#roles-form').checkAll(this.value);
            } else {
                jax('#roles-form').uncheckAll(this.value);
            }
        });
        jax('#roles-form').submit(function(){
            return jax('#roles-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#user-form')[0] != undefined) {
        if (jax('#username').attrib('type') == 'hidden') {
            jax(jax('label[for=username]').parent()).hide();
            jax(jax('#username').parent()).hide();
        }
    }
    if ((jax('#profile-form')[0] != undefined) || (jax('#register-form')[0] != undefined)) {
        if (jax('#username').attrib('type') == 'hidden') {
            jax(jax('label[for=username]').parent()).hide();
            jax(jax('#username').parent()).hide();
        }
    }
    if (jax('#user-search-form')[0] != undefined) {
        jax('#user-search-form').submit(function(){
            var url = this.action;
            if (jax(this.role_id).val() != '----') {
                url = url +  '/' + jax(this.role_id).val();
            }
            if (jax(this.username).val() != '') {
                url = url + '?username=' + encodeURIComponent(jax(this.username).val());
            }
            window.location.href = url;
            return false;
        });
    }
    if ((jax('#role-form')[0] != undefined) && (jax('#id').val() != 0)) {
        if (jax.cookie.load('phire') != '') {
            var phireCookie = jax.cookie.load('phire');
            var json = jax.get(phireCookie.base_path + phireCookie.app_uri + '/roles/json/' + jax('#id').val());
            if (json.length > 0) {
                jax('#resource_1').val(json[0].resource);
                phire.changeActions(jax('#resource_1')[0]);
                if (json[0].action != null) {
                    jax('#action_1').val(json[0].action);
                }
                jax('#permission_1').val(((json[0].permission == 'allow') ? 1 : 0));
                json.shift();
                if (json.length > 0) {
                    phire.addResource(json);
                }
            }
        }
    }
});

