import app from 'flarum/app';
import { extend } from 'flarum/extend';
import ForumApplication from 'flarum/ForumApplication';
import Model from 'flarum/Model';
import User from 'flarum/models/User';
import alertPwnedPassword from './alertPwnedPassword';

app.initializers.add('reflar/pwned-passwords', () => {
  User.prototype.hasPwnedPassword = Model.attribute('hasPwnedPassword');

  extend(ForumApplication.prototype, 'mount', function() {
    alertPwnedPassword(this);
  });
});
