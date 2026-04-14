import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Notices from 'flarum/forum/components/Notices';
import PwnedPasswordNotice from './alertPwnedPassword';

export { default as extend } from './extend';

app.initializers.add('fof/pwned-passwords', () => {
  extend(Notices.prototype, 'items', function (items) {
    const user = app.session.user;

    if (user && user.hasPwnedPassword() && user.isEmailConfirmed()) {
      items.add('pwnedPassword', <PwnedPasswordNotice />, 95);
    }
  });
});
