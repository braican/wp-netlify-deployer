# Netlify Deployer

![Deployer](assets/deployer-icon.png)

Easily trigger a deploy to your Netlify site from WordPress.

## Installation

1. Download this package and unzip.
1. Upload `netlify-deployer` to your `/wp-content/plugins/` directory.
1. Activate the plugin through the "Plugins" menu within WordPress.

Once activated, a "Deployer" menu item will appear in the admin menu. You can head to this admin page to add your Netlify build hook and to deploy.

### Setting up a build hook in Netlify

You'll need a build hook url from Netlify for your project. To set one up, head to your project on Netlify, click on "Settings", and head to the "Build & Deploy" tab. Find the "Build hooks" section, and click the "Add build hook" button. Name it whatever you want, choose a branch to be built, and click "Save" to create the hook. Netlify will provide a url that can be used to trigger a build on the specified branch.

## Development

To work on and make changes to this plugin, first pull down this repo, then work through the following build steps to watch and compile your sass and javascript:

```bash
# Make sure you're using the right node version
nvm install

# Install packages
yarn install

# Start the watch gulp task, which will watch sass and js files
yarn run dev
```

To create build assets, you can run:

```bash
yarn run build
```

## Author

Made by [Nick Braica](https://www.braican.com), who would be happy to talk to you about your next project.

## Changelog

### 0.0.1 `12/5/2018`

* Initial public beta release.