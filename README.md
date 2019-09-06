# Fluid Styleguide – Living Styleguide for TYPO3

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
* zip download
* easy and flexible configuration via [yaml file](./Documentation/ConfigurationReference.md)
* live editing of example data [BETA]

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

    Make sure to define the component namespace in your **ext_localconf.php**:

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

5. Start [building your own components](./Documentation/BuildingComponents.md) using Fluid Components and fixture files

## Documentation

* [Building Components with Fluid Styleguide](./Documentation/BuildingComponents.md)
* [Styleguide Configuration Reference](./Documentation/ConfigurationReference.md)
