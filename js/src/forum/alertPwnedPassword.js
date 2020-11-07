import Alert from 'flarum/components/Alert';
import Button from 'flarum/components/Button';
import icon from 'flarum/helpers/icon';

export default function alertPwnedPassword(app) {
    const user = app.session.user;

    if (!user || !user.hasPwnedPassword()) return;
    if (!user.isEmailConfirmed()) return;

    const resendButton = Button.component(
        {
            className: 'Button Button--link',
            onclick: function() {
                resendButton.props.loading = true;
                m.redraw();

                app.request({
                    method: 'POST',
                    url: app.forum.attribute('apiUrl') + '/forgot',
                    data: { email: user.email() },
                })
                    .then(() => {
                        resendButton.props.loading = false;
                        resendButton.props.children = [
                            icon('fas fa-check'),
                            ' ',
                            app.translator.trans('fof-pwned-passwords.forum.alert.sent_message'),
                        ];
                        resendButton.props.disabled = true;
                        m.redraw();
                    })
                    .catch(() => {
                        resendButton.props.loading = false;
                        m.redraw();
                    });
            },
        },
        app.translator.trans('fof-pwned-passwords.forum.alert.resend_button')
    );

    class ContainedAlert extends Alert {
        view(vnode) {
            const vdom = super.view(vnode);
            return { ...vdom, children: [<div className="container">{vdom.children}</div>] };
        }
    }

    m.mount($('<div/>').insertBefore('#content')[0], {
        view: () => (
            <ContainedAlert dismissible={false} controls={[<resendButton />]}>
                {app.translator.trans('fof-pwned-passwords.forum.alert.warning')}
            </ContainedAlert>
        ),
    });
}
