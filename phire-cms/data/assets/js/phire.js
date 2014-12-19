/**
 * Phire CMS 2 Scripts
 */

var phire = {
    permissionCount : 1,

    addPermissions : function() {
        phire.permissionCount++;

        // Add permission select field
        jax('#permission_new_1').clone({
            "name" : 'permission_new_' + phire.permissionCount,
            "id"   : 'permission_new_' + phire.permissionCount
        }).appendTo(jax('#permission_new_1').parent());

        // Add allow select field
        jax('#allow_new_1').clone({
            "name" : 'allow_new_' + phire.permissionCount,
            "id"   : 'allow_new_' + phire.permissionCount
        }).appendTo(jax('#allow_new_1').parent());

        jax('#permission_new_' + phire.permissionCount).val(jax('#permission_new_' + (phire.permissionCount - 1) + ' > option:selected').val());
        jax('#allow_new_' + phire.permissionCount).val(jax('#allow_new_' + (phire.permissionCount - 1) + ' > option:selected').val());
    },

    changeUsername : function(){
        if ((jax('#username')[0] != undefined) && (jax('#email1')[0] != undefined)) {
            jax('#username').val(jax('#email1').val());
        }
    },

    changeRole : function(id, path) {
        var json = jax.get(path + '/roles/json/' + id);
        if (json.email_as_username != undefined) {
            if ((json.email_as_username) && (jax('#username').attrib('type') == 'text')) {
                jax('label[for=username]').val('&nbsp;');
                jax('#username').attrib('type', 'hidden');
                jax('#username')[0].removeAttribute('required');
                jax('#username').val(jax('#email1').val());
                jax('#email1').on('blur', phire.changeUsername);
            } else if ((!json.email_as_username) && (jax('#username').attrib('type') == 'hidden')) {
                jax('label[for=username]').val('Username');
                jax('#username').attrib('type', 'text');
                jax('#username').attrib('required', 'required');
                jax('#email1').off('blur', phire.changeUsername);
            }
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
    }

};

jax(document).ready(function(){
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
});

