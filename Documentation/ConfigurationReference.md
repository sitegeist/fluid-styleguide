# Fluid Styleguide Configuration Reference

Fluid Styleguide can be configured in various ways by creating a YAML configuration file in your extension or sitepackage:

**Configuration/Yaml/FluidStyleguide.yaml**

Each extension can add its own configuration to the styleguide, all available files will be picked up automatically and merged with the [default configuration file](./Configuration/Yaml/FluidStyleguide.yaml).

## Adding frontend assets

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

## Modifying the component context

While most components can function without a specific context around them, for
some components their context is quite important. For example, a button component
could have a special styling when used on dark backgrounds. In this case the styleguide
should use a dark background as well when this variant of the button is displayed.

By default, Fluid Styleguide uses the following component context, which adds
a 24px space around each component:

```yaml
FluidStyleguide:
    # Markup that will be wrapped around the component output in the styleguide
    # This can be overwritten per component fixture by specifying
    # "styleguideComponentContext" in the fixture data
    ComponentContext: '<div class="fluidStyleguideComponentSpacing">|</div>'
```

The first pipe character within the specified context markup will be replaced with
the component markup.

This context can be modified either globally in your FluidStyleguide.yaml or
individually for each variant of a component in the appropriate fixture file:

Button.fixture.json:

```json
{
    "default": {
        ...
    },
    "onDarkBackground": {
        ...
        "styleguideComponentContext": "<div class=\"myDarkBackground\">|</div>"
    }
}
```

## Enabling and disabling styleguide features

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

## Specifying responsive breakpoints for testing

The default responsive breakpoints can be altered or extended.

```yaml
FluidStyleguide:
    ResponsiveBreakpoints:
        Desktop: '100%'
        Tablet: '800px'
        Mobile: '400px'
```
