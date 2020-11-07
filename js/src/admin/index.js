import { settings } from '@fof-components';
const {
    SettingsModal,
    items: { BooleanItem },
} = settings;

app.initializers.add('fof/pwned-passwords', () => {
    app.extensionSettings['fof-pwned-passwords'] = () =>
        app.modal.show(SettingsModal, {
            title: 'FriendsOfFlarum Pwned Passwords',
            className: 'FofPwnedPasswordsModal',
            items: s => [
                <BooleanItem setting={s} name="fof-pwned-passwords.enableLoginCheck">
                    <span>{app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck')}</span>
                </BooleanItem>,
                <BooleanItem setting={s} name="fof-pwned-passwords.revokeAdminAccess">
                    <span>{app.translator.trans('fof-pwned-passwords.admin.enableAdminRevoke')}</span>
                </BooleanItem>,
            ],
        });
});
