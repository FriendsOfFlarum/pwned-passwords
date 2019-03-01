import Alert from 'flarum/components/Alert';
import Button from 'flarum/components/Button';
import icon from 'flarum/helpers/icon';

export default function alertPwnedPassword(app) {
  const user = app.session.user;

  if (!user || !user.hasPwnedPassword()) return;
  if (!user.isEmailConfirmed()) return;

  const resendButton = Button.component({
    className: 'Button Button--link',
    children: app.translator.trans(
      'reflar-pwned-passwords.forum.alert.resend_button'
    ),
    onclick: function() {
      resendButton.props.loading = true;
      m.redraw();

      app
        .request({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/forgot',
          data: { email: user.email() }
        })
        .then(() => {
          resendButton.props.loading = false;
          resendButton.props.children = [
            icon('fas fa-check'),
            ' ',
            app.translator.trans(
              'reflar-pwned-passwords.forum.alert.sent_message'
            )
          ];
          resendButton.props.disabled = true;
          m.redraw();
        })
        .catch(() => {
          resendButton.props.loading = false;
          m.redraw();
        });
    }
  });

  class ContainedAlert extends Alert {
    view() {
      const vdom = super.view();

      vdom.children = [<div className="container">{vdom.children}</div>];

      return vdom;
    }
  }

  m.mount(
    $('<div/>').insertBefore('#content')[0],
    ContainedAlert.component({
      dismissible: false,
      children: app.translator.trans(
        'reflar-pwned-passwords.forum.alert.warning',
        {
          email: <strong>{user.email()}</strong>
        }
      ),
      controls: [resendButton]
    })
  );
}
