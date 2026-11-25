/* global s9e */
import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';

/**
 * Live preview under the text field editor, shown while the textarea has
 * focus. Like Flarum's composer preview it polls the stream instead of
 * redrawing on every keystroke.
 *
 * It uses the forum's post formatter, the only one available in the browser.
 * Markdown renders the same as on the server; post-only syntax (BBCode,
 * mentions, emoji) is previewed but not formatted after saving.
 */
export default class TextFieldPreview extends Component {
  view() {
    return (
      <div className="MasqueradeTextField-preview" hidden>
        <div className="MasqueradeTextField-previewLabel">{app.translator.trans('flaiming-masquerade-text-field.forum.preview')}</div>
        <div className="MasqueradeTextField-previewBody" />
      </div>
    );
  }

  oncreate(vnode) {
    super.oncreate(vnode);

    const body = vnode.dom.querySelector('.MasqueradeTextField-previewBody');
    const textarea = vnode.dom.parentNode.querySelector('textarea');
    let focused = false;
    let previous = null;

    const update = () => {
      const value = this.attrs.stream() || '';
      const available = typeof s9e !== 'undefined' && s9e.TextFormatter;

      vnode.dom.hidden = !available || !focused || !value.trim();
      if (vnode.dom.hidden || value === previous) return;

      previous = value;
      s9e.TextFormatter.preview(value, body);
    };

    this.onFocus = () => {
      focused = true;
      update();
    };
    this.onBlur = () => {
      focused = false;
      update();
    };
    this.textarea = textarea;
    textarea?.addEventListener('focus', this.onFocus);
    textarea?.addEventListener('blur', this.onBlur);

    this.interval = setInterval(update, 100);
  }

  onremove(vnode) {
    super.onremove(vnode);
    clearInterval(this.interval);
    this.textarea?.removeEventListener('focus', this.onFocus);
    this.textarea?.removeEventListener('blur', this.onBlur);
  }
}
