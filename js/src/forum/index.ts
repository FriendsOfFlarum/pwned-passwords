import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import Application from 'flarum/common/Application';
import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
import alertPwnedPassword from './alertPwnedPassword';

app.initializers.add('fof/pwned-passwords', () => {
  User.prototype.hasPwnedPassword = Model.attribute('hasPwnedPassword');

  extend(Application.prototype, 'mount', function () {
    alertPwnedPassword();
  });
});
