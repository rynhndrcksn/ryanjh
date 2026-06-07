+++
title = "Technology Stack"
description = "The technology stack page for ryanjh.com that lists all the technologies used."
+++

A frequent thought I have while visiting websites is what technologies they're using for it. This page lists
all the technologies used for this site for people who have the same thought.

- Static Site Generator: [Zola](https://www.getzola.org)
- CSS Framework: [Kelp UI](https://kelpui.com)
- Reverse Proxy: [Caddy](https://caddyserver.com)
- Server: [Hetzner CPX11](https://www.hetzner.com/cloud/)

## Why These Choices

When I first started thinking about my personal website and what I wanted out of it, I had the following requirements:

1. Stable.
2. Lightweight.
3. Wouldn't need constant babysitting.

Due to these requirements, I made the decision to pick each technology for various reasons.

### Zola

Originally this site was powered by Symfony with a Postgres database.
However, after ~6 months, it started to feel ridiculously overkill for what I was trying to do on this site.
Zola is a single binary static site generator written in Rust that compiles Markdown content into static HTML.
There's no runtime to manage, no dependencies to update, and no containers to babysit.
Deployments are a single `rsync` to the server.
Zola's built-in support for sitemaps, RSS feeds, and taxonomies (if needed) means I'm not giving anything up for a site of this scope.

### Kelp UI

I know enough CSS to be dangerous, but I knew in order for me to be truly productive, I'd need to rely on a CSS framework/UI library.
Thankfully, [Chris Ferdinandi](https://gomakethings.com) made a fantastic and lightweight UI library called "Kelp UI" and open-sourced it.
It comes in at a whopping 12.1 kilobytes and is incredibly easy to extend when necessary.
Plus, it natively supports light and dark modes and looks great with minimal customizations!

### Caddy

Switching from Nginx to Caddy was a natural fit once the stack moved to static files.
Caddy handles automatic TLS certificate provisioning and renewal out of the box with zero configuration, which means no Certbot, no cron jobs, no manual cert management.
The Caddyfile syntax is dramatically simpler than Nginx's configuration format, and for a static file server there's nothing Nginx offered that Caddy doesn't.
The one thing I gave up is Nginx's built-in page caching, but that's irrelevant when Caddy is serving pre-built static files directly off disk.

### Hetzner

I know some people are probably thinking, "why not use AWS/GCP/Cloudflare Pages?"
The short answer? I don't want to.
The long answer? I'm paying Hetzner ~$6.15 a month for a server with 2 VCPUs and 2GB of RAM.
I can't even think about using a big cloud provider for that cheap.
Do I have high-availability and multi-region deployments? No.
Do I need either one of those things? No.
With Caddy serving static files, this modest server handles requests trivially.
If for some reason a bunch of people visit this website and crash it, then oh well.
It'll recover eventually and be back up.
