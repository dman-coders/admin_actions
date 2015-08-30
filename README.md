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

 
# Implementation

The actions cannot be inserted into the edit form itself directly
 (eg, in a vertical tab)
 as HTML does not allow nested forms, and the form being used here
 (from Views Bulk Operations) is its own thing.
Ideally this utility may be better integrated via some other method, the 'block'
 method is just a low-impact way of leveraging existing VBO functionality.
 
This is intentionally minimal code, leveraging VBO+Views 99.9% without
 adding new code. As such, it's a bit clunky.
To further refine the behaviour, we'd need to re-imagine the task and build
 it our own way.