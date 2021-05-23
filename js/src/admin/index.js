import app from 'flarum/common/app';

app.initializers.add('fof/pwned-passwords', () => {
    app.extensionData
        .for('fof-pwned-passwords')
        .registerSetting({
            label: app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck'),
            setting: 'fof-pwned-passwords.enableLoginCheck',
            type: 'boolean',
        })
        .registerSetting({
            label: app.translator.trans('fof-pwned-passwords.admin.enableAdminRevoke'),
            setting: 'fof-pwned-passwords.revokeAdminAccess',
            type: 'boolean',
        });
});
