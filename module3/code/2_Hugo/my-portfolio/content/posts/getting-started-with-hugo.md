---
title: "Getting Started with Hugo"
date: 2024-01-15
description: "How a Markdown folder becomes a website"
draft: false
---

Hugo turns Markdown files into a static website. This is the short version of how I started.

## Why Hugo

- I write in Markdown and keep the site in Git.
- There is no database and nothing to keep patched.
- Builds are fast, and GitHub Pages hosts the result for free.

## Steps

1. Install Hugo.
2. Edit the Markdown files in `content/`.
3. Preview with `hugo server -D`.
4. Build with `hugo --minify`. The site is written to `public/`.

## Resources

- [Hugo documentation](https://gohugo.io/documentation/)
- [Markdown guide](https://www.markdownguide.org/)
