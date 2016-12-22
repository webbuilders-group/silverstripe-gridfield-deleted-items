Usage
========================
First to use the GridField Deleted Items components your target DataObject must have the ``Versioned`` extension or the component will not work. Next you need to include the components in your GridField. When using the ``GridFieldConfig_RecordEditor`` as a base config you will also need to remove ``GridFieldDataColumns`` and ``GridFieldEditButton`` as you will need to replace them with the GridField Deleted Items versions. For example:

```php
$gridField=new GridField('MyRelationship', 'My Relationship', $this->MyRelationship(), GridFieldConfig_RecordEditor::create(10));
$gridField->getConfig()
                    ->removeComponentsByType('GridFieldDataColumns')
                    ->removeComponentsByType('GridFieldEditButton')
                    ->addComponent(new GridFieldDeletedManipulator(), 'GridFieldToolbarHeader')
                    ->addComponent(new GridFieldDeletedColumns())
                    ->addComponent(new GridFieldDeletedEditButton())
                    ->addComponent(new GridFieldDeletedRestoreButton())
                    ->addComponent(new GridFieldDeletedToggle('buttons-before-left'));
```

Since you are using versioned you probably have a draft and a live state with special controls on the edit screen for working with those states. If so you probably also want to remove the delete action as users will end up in a situation where they've deleted the draft but not the live. As well this module does not supply an extension of the delete action that removes it when the record is deleted. To do this simply do the following this will remove the delete action.

```php
$gridField->getConfig()->removeComponentsByType('GridFieldDeleteAction');
```
