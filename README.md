# Synopsis

This features exposes more buttons for performing single actions upon a node,
 eg, 'rebuild node alias' or 'set field value to x'.

These actions are most useful for non-core actions and custom actions, such as
 'check for broken links', 'convert to page',
 or 'set expiry date 3 months into the future'.
 
This UI does not supply the actions themselves, just shifts them from
 VBO into the node edit form directly for easier access.

# Installation

This feature provides a *block* that is to be displayed underneath
 node (entity) edit forms.
In some cases, if using a custom admin theme, this may need to be placed
 manually using block management UI.
 /admin/structure/block/manage/views/admin_actions-admin_block/configure
 
When enabled, a few example core actions (sticky,promote) are already on the
 utility to show it working.
 You probably won't want them, so it's OK to remove them and add your own.
 
Although the natural place to perform admin actions is on node edit forms, 
 you can also use the block positioning rules to have it show up on node
 full-view pages, so you can 'promote to front page' or 'unpublish' an item
 directly from the front-end.
 Access to these buttons will be handled by user role permissions set on the
 view, so you would not expect to allow anonymous users to see these tasks.
 Granualr control is available to actions if you enable 
 "Actions permissions (VBO)"
 
# Adding actions

You are expected to edit the view yourself at
 /admin/structure/views/view/admin_actions
 Under 'Fields', select 'Bulk operations: Content (Content)'
 and select from the available actions.
More actions can become available from other modules that implement
 hook_action_info() 
Local actions can also be configured at /admin/config/system/actions

You should probably select 'skip confirmation step' when adding actions.

If your action needs settings, the settings form will be displayed 
 in the same place on the page as the buttons, which may be a little awkward.

# Extending control

The initial setup provides one set of VBO buttons and makes it available
 for all node types.

## Restrict to certain nodes

Is easiest done by just changing the "Content Types" restriction at
 `/admin/structure/block/manage/views/admin_actions-admin_block/configure`

## Different buttons for different content types

For more advanced control, you may want to *clone* the block in the Views UI
`/admin/structure/views/view/admin_actions/edit/admin_block`
and adjust the allowed "Bulk Operations" buttons to create a new set of actions.
Place this block nearby your target content type, and customize from there.
 
# Implementation

### Limitations
The actions cannot be inserted into the edit form itself directly
 (eg, in a vertical tab)
 as HTML does not allow nested forms, and the form being used here
 (from Views Bulk Operations) is its own thing.
Ideally this utility may be better integrated via some other method.
The 'block' method is just a low-impact way of leveraging
 existing VBO functionality and already-existing
 Drupal site building conventions.

### Drupal-native site-building methodology

Most of the functionality described is just leveraging things that VBO
 and core already provide you. 
 This module just provides you with a starting point for your own modifications.

This module is intentionally minimal code, leveraging VBO+Views 99.9% without
 adding new code. As such, it's a bit clunky in some ways,
 but should feel familiar in other ways.
To further refine the behaviour, we'd need to re-imagine the task and build
 it our own way.