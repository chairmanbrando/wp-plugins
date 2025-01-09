So, you want to style a page or post and then duplicate it, eh? Good luck!

- Problem 1: Using a post's ID for styling means duplicating that post causes your styles to break.

- Problem 2: Using a post's title for styling means renaming or even setting that post to private causes your styles to break.

- Problem 3: Using a post's slug *can* work so it doesn't necessarily change with a title change, but duplication does change it, breaking your styles, if only temporarily.

- Problem 4: Requiring a template to be used for every post just to apply some class for styling is quite a silly solution.

- Problem 5: WP's GUID field isn't static like you might expect, changing when you duplicate a post, so using that doesn't stop your styles from breaking either.

- Problem 6: Adding CSS to a post as metadata works, but it forces you to scatter your styles around instead of keeping them in a single place. Plus, you have to edit code in a crappy textarea.

The solution? UUIDs that actually stay in place through duplication! That's what this plugin does. Are they ugly and unclear compared to other types of body classes? Yes, `.uuid-a7d57436a1d22999e067f8b9edd6bd96` is certainly uglier than `.about-us`, but that's what comments and/or Sass includes are for!

**An important note about Yoast Duplicate Post...**

While this UUID plugin *does* work with [Duplicate Page](https://wordpress.org/plugins/duplicate-page), as far as I can tell it doesn't work with [Yoast Duplicate Post](https://wordpress.org/plugins/duplicate-post/). I haven't yet figured out why. They're both meant to copy post metadata as it exists into the new duplicate post, but Yoast's seems not to do that.

I about drove myself bonkers trying to debug it, but no matter what I tried, the UUID *always* changed for a duplicated post or page using Yoast's plugin. I wonder if instead of duplicating metadata it's *recreating* it in such a way that this time-based code -- `uniqid()` -- runs again.
