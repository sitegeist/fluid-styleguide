# Fluid Styleguide

Fluid Styleguide is a design collaboration tool for TYPO3 projects. It supports frontend developers in creating reusable
components and stimulates effective communication across all project stakeholders.

## Target Audiences

Fluid Styleguide can be a useful tool for all project stakeholders:

* **designers and frontend developers** can improve their quality assurance processes
* **frontend, backend and integration** discuss and coordinate data structures and interfaces between the stacks
* **project managers and product owners** see the current state of the project's components
* for **clients** the project gets more transparent

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

5. Start building your own components

    See [How do components look like?](https://github.com/sitegeist/fluid-components#how-do-components-look-like)

## Advanced Configuration

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
