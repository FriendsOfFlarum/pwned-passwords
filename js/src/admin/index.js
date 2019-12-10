import { settings } from '@fof-components';
const {
    SettingsModal,
    items: { BooleanItem },
} = settings;

app.initializers.add('fof/pwned-passwords', () => {
    app.extensionSettings['fof-pwned-passwords'] = () => app.modal.show(new SettingsModal({
        title: 'FriendsOfFlarum Pwned Passwords',
        className: 'FofPwnedPasswordsModal',
        items: [
            <BooleanItem key='fof-pwned-passwords.enableLoginCheck'>
                <span>{app.translator.trans('fof-pwned-passwords.admin.enableLoginCheck')}</span>
            </BooleanItem>
        ]
    }))
})