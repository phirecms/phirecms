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
    },

    addResource : function(path) {
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

        // Add allow select field
        jax('#allow_new_1').clone({
            "name" : 'allow_new_' + phire.resourceCount,
            "id"   : 'allow_new_' + phire.resourceCount
        }).appendTo(jax('#allow_new_1').parent());

        jax('#resource_new_' + phire.resourceCount).val(jax('#resource_new_' + (phire.resourceCount - 1) + ' > option:selected').val());
        phire.changePermissions(jax('#resource_new_' + phire.resourceCount)[0], path, false);
    },

    changePermissions : function(sel, path, cur) {
        var cur   = (cur) ? 'cur' : 'new';
        var id    = sel.id.substring(sel.id.lastIndexOf('_') + 1);
        var opts  = jax('#permission_' + cur + '_' + id + ' > option').toArray();
        var start = opts.length - 1;
        for (var i = start; i >= 0; i--) {
            jax(opts[i]).remove();
        }
        jax('#permission_' + cur + '_' + id).append('option', {"value" : '----'}, '----');

        if (jax(sel).val() != '----') {
            var json = jax.get(path + '/users/roles/json/' + jax(sel).val());
            if (json.permissions != undefined) {
                for (var i = 0; i < json.permissions.length; i++) {
                    jax('#permission_' + cur + '_' + id).append('option', {"value" : json.permissions[i]}, json.permissions[i]);
                }
            }
        }
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
    if (jax('#user-roles-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#user-roles-form').checkAll(this.value);
            } else {
                jax('#user-roles-form').uncheckAll(this.value);
            }
        });
        jax('#user-roles-form').submit(function(){
            return jax('#user-roles-form').checkValidate('checkbox', true);
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
});

