import { VnodeDOM } from 'mithril';
import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import icon from 'flarum/common/helpers/icon';

// Based on Flarum's forum/util/alertEmailConfirmation
export default function alertPwnedPassword() {
  const user = app.session.user;

  if (!user || !user.hasPwnedPassword()) return;

  // Don't show if email is unconfirmed, as it would cause multiple similar alerts
  // Also, it's not supposed to be possible once the extension is enabled
  if (!user.isEmailConfirmed()) return;

  class ResendButton extends Component {
    loading: boolean = false;
    sent: boolean = false;

    view() {
      return (
        <Button class="Button Button--link" onclick={this.onclick.bind(this)} loading={this.loading} disabled={this.sent}>
          {this.sent
            ? [icon('fas fa-check'), ' ', app.translator.trans('fof-pwned-passwords.forum.alert.sent_message')]
            : app.translator.trans('fof-pwned-passwords.forum.alert.resend_button')}
        </Button>
      );
    }

    onclick() {
      this.loading = true;
      m.redraw();

      app
        .request({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/forgot',
          body: { email: user!.email() },
        })
        .then(() => {
          this.loading = false;
          this.sent = true;
          m.redraw();
        })
        .catch(() => {
          this.loading = false;
          m.redraw();
        });
    }
  }

  class ContainedAlert extends Alert {
    view(vnode: VnodeDOM) {
      const vdom = super.view(vnode);
      return { ...vdom, children: [<div className="container">{vdom.children}</div>] };
    }
  }

  m.mount($('<div/>').insertBefore('#content')[0], {
    view: () => (
      <ContainedAlert dismissible={false} controls={[<ResendButton />]}>
        {app.translator.trans('fof-pwned-passwords.forum.alert.warning')}
      </ContainedAlert>
    ),
  });
}
