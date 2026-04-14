/// <reference types="mithril" />
import Component from 'flarum/common/Component';
export default class PwnedPasswordNotice extends Component {
    loading: boolean;
    sent: boolean;
    view(): JSX.Element;
    onclick(): void;
}
