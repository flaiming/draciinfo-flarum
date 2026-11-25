import app from 'flarum/admin/app';
import { extend } from 'flarum/common/extend';
import FieldEditModal from 'ext:fof/masquerade/admin/components/FieldEditModal';

app.initializers.add('flaiming-masquerade-text-field', () => {
  extend(FieldEditModal.prototype, 'availableTypes', function (types) {
    types.text = app.translator.trans('flaiming-masquerade-text-field.admin.types.text');
  });
});
