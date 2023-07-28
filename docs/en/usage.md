Usage
========================
First to use the GridField Deleted Items components your target DataObject must have the ``SilverStripe\Versioned\Versioned`` extension or the component will not work. Next you need to include the components in your GridField. When using the ``GridFieldConfig_RecordEditor`` as a base config you will also need to remove ``GridFieldDataColumns`` and ``GridFieldEditButton`` as you will need to replace them with the GridField Deleted Items versions. For example:

```php
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedColumns;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedEditButton;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedRestoreButton;
use WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedToggle;

/* ... */
$gridField=new GridField('MyRelationship', 'My Relationship', $this->MyRelationship(), GridFieldConfig_RecordEditor::create(10));
$gridField->getConfig()
    ->removeComponentsByType(GridFieldDataColumns::class)
    ->removeComponentsByType(GridFieldEditButton::class)
    ->addComponent(new GridFieldDeletedManipulator(), GridFieldToolbarHeader::class)
    ->addComponent(new GridFieldDeletedColumns(), GridFieldDeleteAction::class)
    ->addComponent(new GridFieldDeletedEditButton(), GridFieldDeleteAction::class)
    ->addComponent(new GridFieldDeletedRestoreButton(), GridFieldDeleteAction::class)
    ->addComponent(new GridFieldDeletedToggle('buttons-before-left'));
```
Since you are using versioned you probably have a draft and a live state with special controls on the edit screen for working with those states. If so you probably also want to remove the delete action as users will end up in a situation where they've deleted the draft but not the live. To do this simply do the following this will remove the delete action.

```php
$gridField->getConfig()->removeComponentsByType(GridFieldDeleteAction::class);
```

Optionally you could remove and replace the delete action with the ``WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedDeleteAction`` component that removes itself if the record is deleted, just be sure to pass true into the constructor when working with a many_many relationship. As well if you define a ``CMSEditLink`` method on your model object when the restore completes you will be redirected to that link. When working with versioned in most cases `SilverStripe\Versioned\GridFieldArchiveAction` so instead you would remove and replace the archive action with the ``WebbuildersGroup\GridFieldDeletedItems\Forms\GridFieldDeletedArchiveAction`` component that removes itself if the record is archived, just be sure to pass true into the constructor when working with a many_many relationship. As well if you define a ``CMSEditLink`` method on your model object when the restore completes you will be redirected to that link.
