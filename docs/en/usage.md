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

Since you are using versioned you probably have a draft and a live state with special controls on the edit screen for working with those states. If so you probably also want to remove the delete action as users will end up in a situation where they've deleted the draft but not the live. To do this simply do the following this will remove the delete action.

```php
$gridField->getConfig()->removeComponentsByType('GridFieldDeleteAction');
```

Optionally you could remove and replace the delete action with the ``GridFieldDeletedDeleteAction`` component that removes itself if the record is deleted, just be sure to pass true into the constructor when working with a many_many relationship.

As well your model object for the GridField must declare the ``getIsDeletedFromStage`` and the ``getExistsOnLive`` methods, see below for an example of this methods.

```php
/**
 * Compares current draft with live version, and returns TRUE if no draft version of this page exists, but the page is still published (after triggering "Delete from draft site" in the CMS).
 * @return bool
 */
public function getIsDeletedFromStage() {
    if(!$this->ID) {
        return true;
    }

    if(!$this->exists()) {
        return false;
    }

    $stageVersion=Versioned::get_versionnumber_by_stage($this->class, 'Stage', $this->ID);

    // Return true for both completely deleted pages and for pages just deleted from stage.
    return !($stageVersion);
}

/**
 * Return true if this page exists on the live site
 */
public function getExistsOnLive() {
    return (bool)Versioned::get_versionnumber_by_stage($this->class, 'Live', $this->ID);
}
```

Optionally if you define a ``CMSEditLink`` method on your model object when the restore completes you will be redirected to that link.
