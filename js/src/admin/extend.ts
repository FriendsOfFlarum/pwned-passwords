// .for('fof-pwned-passwords')
//   .registerSetting({
//     label: app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck'),
//     setting: 'fof-pwned-passwords.enableLoginCheck',
//     type: 'boolean',
//   })
//   .registerSetting({
//     label: app.translator.trans('fof-pwned-passwords.admin.enableAdminRevoke'),
//     setting: 'fof-pwned-passwords.revokeAdminAccess',
//     type: 'boolean',
//   });

import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

export default [
  new Extend.Admin()
    .setting(
      () => ({
        setting: 'fof-pwned-passwords.enableLoginCheck',
        type: 'boolean',
        label: app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck', {}, true),
      })
    )
    .setting(
      () => ({
        setting: 'fof-pwned-passwords.revokeAdminAccess',
        type: 'boolean',
        label: app.translator.trans('fof-pwned-passwords.admin.enableAdminRevoke', {}, true),
      })
    )
]
