import Extend from 'flarum/common/extenders';
import app from 'flarum/admin/app';

export default [
  new Extend.Admin()
    .setting(() => ({
      setting: 'fof-pwned-passwords.enableLoginCheck',
      type: 'boolean',
      label: app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck', {}, true),
    }))
    .setting(() => ({
      setting: 'fof-pwned-passwords.revokeAdminAccess',
      type: 'boolean',
      label: app.translator.trans('fof-pwned-passwords.admin.enableAdminRevoke', {}, true),
    }))
    .setting(() => ({
      setting: 'fof-pwned-passwords.learnMoreUrl',
      type: 'text',
      label: app.translator.trans('fof-pwned-passwords.admin.learnMoreUrl.label', {}, true),
      help: app.translator.trans('fof-pwned-passwords.admin.learnMoreUrl.help', {}, true),
      placeholder: 'https://haveibeenpwned.com/Passwords',
    })),
];
