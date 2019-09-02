# Fluid Styleguide

Fluid Styleguide is a design collaboration tool for TYPO3 projects. It supports frontend developers in creating reusable
components and encourages effective communication across all project stakeholders.

## Target Groups

Fluid Styleguide can be a useful tool for all project stakeholders:

* **designers and frontend developers** can improve their development and QA workflows
* **frontend, backend and integration** discuss and coordinate data structures and interfaces between the stacks
* **project managers and product owners** see the current state of the project's components
* **clients** get more transparency of the project status

## Features

* visualization of project components
* isolated development of components
* responsive testing
* integrated component documentation
* live editing of example data
* zip download
* easy and flexible configuration (yaml file)

## Getting Started

Just follow these simple steps to get started with the styleguide:

1. Install Fluid Styleguide

    via composer:

        composer require sitegeist/fluid-styleguide

    or download the extension from TER:

    [TER: fluid_styleguide](https://extensions.typo3.org/extension/fluid_styleguide/)

2. Test Fluid Styleguide with demo components

    Just open the page `/fluid-styleguide/` in your TYPO3 installation:

        https://my-domain.tld/fluid-styleguide/

To add your own components to the styleguide, just follow these additional steps:

3. Configure Fluid Components

    Define the component namespace in your **ext_localconf.php**:

    ```php
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['fluid_components']['namespaces']['VENDOR\\MyExtension\\Components'] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('my_extension', 'Resources/Private/Components');
    ```

    Use your own vendor name for `VENDOR`, extension name for `MyExtension`, and extension key for `my_extension`.

4. Configure your frontend assets

    Create a styleguide configuration file in your extension or sitepackage.
    
    **Configuration/Yaml/FluidStyleguide.yaml:**

    ```yaml
    FluidStyleguide:
        ComponentAssets:
            Packages:
                'Vendor\MyExtension\Components':
                    Css:
                        - 'EXT:my_extension/Resources/Public/Css/Main.min.css'
                    Javascript:
                        - 'EXT:my_extension/Resources/Public/Javascript/Main.min.js'
    ```

    Use your own vendor name for `VENDOR`, extension name for `MyExtension`, and extension key for `my_extension`.
    Adjust the paths to the assets according to your directory structure.

5. Start building your own components using Fluid Components and fixture files

## Building Components with Fluid Styleguide

Components must meet the following requirements for them to show up in the styleguide correctly:

1. The component namespace must be registered for Fluid Components (as shown in "Getting Started")
2. The component folder must contain a fixture file which at least contains a `default` fixture (see below).
3. To load your frontend assets, you need to specify them in the FluidStyleguide.yaml configuration file
(as shown in "Getting Started")

### Example Component

**Molecule/Teaser/Teaser.html:**

```xml
<fc:component>
    <fc:param name="title" type="string" />
    <fc:param name="description" type="string" />
    <fc:param name="link" type="SMS\FluidComponents\Domain\Model\Typolink" />
    <fc:param name="icon" type="string" optional="1" />
    <fc:param name="theme" type="string" optional="1" default="light" />

    <fc:renderer>
        <a href="{link}" class="teaser teaser--{theme}">
            <h3 class="teaser__title">{title}</h3>
            <p class="teaser__description">{description}</p>

            <f:if condition="{icon}">
                <my:atom.icon icon="{icon}" class="teaser__icon" />
            </f:if>
        </a>
    </fc:renderer>
</fc:component>
```

For further instructions on how to build components, please refer to the [documentation of Fluid Components](https://github.com/sitegeist/fluid-components).

### Adding fixtures to your component

Each component in the styleguide needs a fixture file which contains example values for all of the component's required arguments.
A fixture file must at least contain a `default` fixture, but it may define additional fixtures that can be selected
in the styleguide interface.

**Molecule/Teaser/Teaser.fixture.json:**

```json
{
    "default": {
        "title": "TYPO3",
        "description": "The professional, flexible Content Management System",
        "link": "https://typo3.org",
        "icon": "typo3"
    },
    "withoutIcon": {
        "title": "TYPO3",
        "description": "The professional, flexible Content Management System",
        "link": "https://typo3.org"
    },
    "dark": {
        "title": "TYPO3",
        "description": "The professional, flexible Content Management System",
        "link": "https://typo3.org",
        "theme": "dark"
    }
}
```

Note that the fixture file must be named exactly like component folder (in this case `Teaser`).

### Adding documentation to your component

If you want to add further documentation to your component, just place a markdown file that is named like your component
inside your component folder. Fluid Styleguide will pick up the documentation automatically and render it in the DOC tab.

**Molecule/Teaser/Teaser.md:**

```markdown
## Teaser Component

This is a generic teaser components. It supports both a light and a dark styling. [...]
```

Note that the documentation file must be named exactly like component folder (in this case `Teaser`).

## Styleguide Configuration Reference

Fluid Styleguide can be configured in various ways by creating a YAML configuration file in your extension or sitepackage:

**Configuration/Yaml/FluidStyleguide.yaml**

Each extension can add its own configuration to the styleguide, all available files will be picked up automatically and merged with the [default configuration file](./Configuration/Yaml/FluidStyleguide.yaml).

### Adding frontend assets

Each component package (= a folder containing multiple components in a directory structure) usually brings its own frontend assets
that define the design (CSS) and behavior (JavaScript) of the components.

Assets can be defined per component package:

```yaml
FluidStyleguide:
    ComponentAssets:
        Packages:
            'Vendor\MyExtension\Components':
                Css:
                    - EXT:my_extension/Resources/Public/Css/Components.css
                Javascript:
                    - EXT:my_extension/Resources/Public/Javascript/Components.js
```

Assets can also be added globally:

```yaml
FluidStyleguide:
    ComponentAssets:
        Global:
            Css:
                - EXT:my_extension/Resources/Public/Css/Global.css
            Javascript:
                - EXT:my_extension/Resources/Public/Javascript/Global.min.js
```

### Enabling and disabling styleguide features

Specific features of the styleguide can be enabled and disabled:

```yaml
FluidStyleguide:
    Features:
        # Enable/Disable markdown documentation rendering
        Documentation: true

        # Enable/Disable live editing of component fixture
        Editor: true

        # Enable/Disable zip download of component folder
        ZipDownload: false

        # Enable/Disable breakpoint switcher
        ResponsiveBreakpoints: true

        # Enable/Disable rulers
        Ruler: false

        # Escapes string input from the editor. This prevents Cross-Site-Scripting
        # but leads to differing component output when using the editor.
        EscapeInputFromEditor: true

        # Show demo components in styleguide even if other components exist
        DemoComponents: false
```

### Specifying responsive breakpoints for testing

The default responsive breakpoints can be altered or extended.

```yaml
FluidStyleguide:
    ResponsiveBreakpoints:
        Desktop: '100%'
        Tablet: '800px'
        Mobile: '400px'
```
