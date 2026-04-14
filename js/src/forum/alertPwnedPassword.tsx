import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Alert from 'flarum/common/components/Alert';
import Button from 'flarum/common/components/Button';
import Icon from 'flarum/common/components/Icon';
import LinkButton from 'flarum/common/components/LinkButton';

export default class PwnedPasswordNotice extends Component {
  loading: boolean = false;
  sent: boolean = false;

  view() {
    const url = app.forum.attribute<string>('fofPwnedPasswordsLearnMoreUrl');
    const forumBase = app.forum.attribute<string>('baseUrl');
    const isExternal = new URL(url).origin !== new URL(forumBase).origin;

    return (
      <Alert
        type="error"
        dismissible={false}
        containerClassName="container"
        controls={[
          <Button className="Button Button--link" onclick={this.onclick.bind(this)} loading={this.loading} disabled={this.sent}>
            {this.sent
              ? [<Icon name="fas fa-check" />, ' ', app.translator.trans('fof-pwned-passwords.forum.alert.sent_message')]
              : app.translator.trans('fof-pwned-passwords.forum.alert.resend_button')}
          </Button>,
          <LinkButton
            className="Button Button--link"
            href={url}
            external={true}
            target={isExternal ? '_blank' : undefined}
            rel={isExternal ? 'noopener noreferrer' : undefined}
          >
            {app.translator.trans('fof-pwned-passwords.forum.alert.learn_more_button')}
          </LinkButton>,
        ]}
      >
        {app.translator.trans('fof-pwned-passwords.forum.alert.warning')}
      </Alert>
    );
  }

  onclick() {
    const user = app.session.user!;

    this.loading = true;
    m.redraw();

    app
      .request({
        method: 'POST',
        url: app.forum.attribute<string>('apiUrl') + '/forgot',
        body: { email: user.email() },
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
