import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import Stream from 'flarum/common/utils/Stream';
import UserCard from 'flarum/forum/components/UserCard';
import TypeFactory from 'ext:fof/masquerade/forum/types/TypeFactory';
import ProfilePane from 'ext:fof/masquerade/forum/panes/ProfilePane';
import TextField, { TEXT_TYPE } from './TextField';

const renderedHtml = (answer) => (answer && answer.attribute('contentHtml')) || null;

app.initializers.add('flaiming-masquerade-text-field', () => {
  // Fields of type "text" become a TextField; the profile editor
  // (ProfileConfigurePane) gets a textarea with a live preview through this.
  // Not added to TypeFactory.types(): Masquerade also matches those keys
  // against validation rules of untyped fields to guess their type.
  override(TypeFactory, 'typeForField', function (original, attrs) {
    if (attrs.field.type() === TEXT_TYPE) return new TextField(attrs);

    return original(attrs);
  });

  // Profile page: Masquerade only passes the source text to field types,
  // hand the rendered HTML to text fields instead.
  override(ProfilePane.prototype, 'field', function (original, field, content) {
    if (field.type() !== TEXT_TYPE) return original(field, content);

    const answer = this.answers.find((a) => a.field()?.id() === field.id());

    return new TextField({ field, stream: Stream(content || ''), html: renderedHtml(answer) }).answerField();
  });

  // User card (fields shown "on bio"): same, replacing the item Masquerade added.
  extend(UserCard.prototype, 'infoItems', function (items) {
    if (!app.forum.attribute('canViewMasquerade')) return;

    (this.attrs.user.bioFields() || []).forEach((answer) => {
      const field = answer.field();
      const key = `masquerade-bio-${field?.id()}`;
      if (!field || field.type() !== TEXT_TYPE || !items.has(key)) return;

      const element = new TextField({ field, stream: Stream(answer.content() || ''), html: renderedHtml(answer) }).answerField();

      if (element) items.setContent(key, element);
      else items.remove(key);
    });
  });
});
