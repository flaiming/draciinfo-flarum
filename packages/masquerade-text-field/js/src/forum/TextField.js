import app from 'flarum/forum/app';
import Icon from 'flarum/common/components/Icon';
import FormGroup from 'flarum/common/components/FormGroup';
import classList from 'flarum/common/utils/classList';
import BaseField from 'ext:fof/masquerade/forum/types/BaseField';
import TextFieldPreview from './TextFieldPreview';

export const TEXT_TYPE = 'text';

/**
 * A multi-line Masquerade field formatted with Markdown.
 *
 * `stream` holds the source text (used by the editor). `html` is the
 * server-rendered answer (the `contentHtml` attribute); Masquerade only hands
 * field types the source, so display code passes it in explicitly.
 */
export default class TextField extends BaseField {
  constructor({ field, stream, html = null }) {
    super({ field, stream });
    this.html = html;
  }

  editorField() {
    return (
      <div className="MasqueradeTextField-editor">
        <FormGroup stream={this.stream} {...this.editorInputAttrs()} />
        <TextFieldPreview stream={this.stream} />
      </div>
    );
  }

  editorInputAttrs() {
    return { ...super.editorInputAttrs(), type: 'textarea', rows: 6 };
  }

  answerField() {
    if (app.forum.attribute('masquerade.hide-empty-fields') && !this.hasAnswer()) {
      return null;
    }

    const iconName = this.field.icon();

    return (
      <div className={classList('Masquerade-Bio-Set', 'Masquerade-Bio-Set--text', !this.hasAnswer() && 'Masquerade-Bio-Set--empty')}>
        <span className="Masquerade-Bio-Field">
          {iconName && [<Icon name={iconName} />, ' ']}
          {this.field.name()}:{' '}
        </span>
        <div className="Masquerade-Bio-Answer Masquerade-Bio-Answer--text">{this.answerContent()}</div>
      </div>
    );
  }

  answerContent() {
    // Rendered by the server with an allow-list formatter, safe to trust.
    if (this.html) return m.trust(this.html);

    // No rendered HTML available: show the source as plain text. Must return
    // null for a blank answer, otherwise hasAnswer() would see a vnode.
    const value = this.stream();
    return value && value.trim() ? <div className="MasqueradeTextField-plain">{value}</div> : null;
  }
}
