import app from 'flarum/common/app';
import { extend } from 'flarum/common/extend';
import ForumApplication from 'flarum/forum/ForumApplication';
import Model from 'flarum/common/Model';
import User from 'flarum/common/models/User';
import alertPwnedPassword from './alertPwnedPassword';

app.initializers.add('fof/pwned-passwords', () => {
    User.prototype.hasPwnedPassword = Model.attribute('hasPwnedPassword');

    extend(ForumApplication.prototype, 'mount', function () {
        alertPwnedPassword(this);
    });
});
