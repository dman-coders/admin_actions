# Synopsis

This features exposes more buttons for performing single actions upon a node,
 eg, 'rebuild node alias' or 'set field value to x'.

These actions are most useful for non-core actions and custom actions, such as
 'check for broken links', 'convert to page',
 or 'set expiry date 3 months into the future'.

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
 actions, so you would not expect to allow anonymous users to see these tasks.
 
# Adding actions

You are expected to edit the list of available buttons in the block config
form.

More actions can become available from other modules that implement
 hook_action_info() 

Local actions can also be configured at /admin/config/system/actions

You should probably select 'skip confirmation step' when adding actions.

If your action needs settings, the settings form will be displayed 
 in the same place on the page as the buttons, which may be a little awkward.
 Atomic actions that require further questions should probably not be used
 in this context, though they are still supported.

# Extending control

## Per Entity Type

The initial setup provides one set of buttons and makes it available
 for all node types. This is just meant as a getting-started example.

### Restrict to certain nodes

Is easiest done by just changing the "Content Types" restriction at
 `/admin/structure/block/manage/views/admin_actions-admin_block/configure`

### Different buttons for different content types

For more advanced control, you may want to *clone* the block in the Views UI
`/admin/structure/views/view/admin_actions/edit/admin_block`
and adjust the allowed "Bulk Operations" buttons to create a new set of actions.
Place this block nearby your target content type, and customize from there.

## Permissions

Access to these buttons is best managed through the block admin UI for 
 block visibility.
 Additionally, actions which have their own access restrictions via role
 permissions should only show up when the user is allowed.

# Implementation

### Drupal-native site-building methodology

Most of the functionality described is just leveraging things that core, 
 Rules, and VBO already provide you. 

