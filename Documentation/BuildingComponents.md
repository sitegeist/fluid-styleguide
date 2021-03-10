# Building Components with Fluid Styleguide

Components must meet the following requirements for them to show up in the styleguide correctly:

1. The **component namespace must be registered** for Fluid Components (as shown in [Getting Started](../README.md))
2. The component folder must contain a **fixture file** which at least contains the `default` fixture (see below).
3. To load your **frontend assets**, you need to specify them in the FluidStyleguide.yaml configuration file
(as shown in [Getting Started](../README.md))

## Example Component

For illustration purposes we want to add the following component to the styleguide:

**Molecule/Teaser/Teaser.html:**

```xml
<fc:component>
    <fc:param name="title" type="string" />
    <fc:param name="link" type="SMS\FluidComponents\Domain\Model\Typolink" />
    <fc:param name="image" type="SMS\FluidComponents\Domain\Model\Image" optional="1" />
    <fc:param name="actions" type="string" optional="1" />
    <fc:param name="theme" type="string" optional="1" default="light" />

    <fc:renderer>
        <a href="{link}" class="teaser teaser--{theme}" target="{link.target}">
            <h3 class="teaser__title">{title}</h3>
            <f:if condition="{content}">
                <p class="teaser__content">{content}</p>
            </f:if>

            {actions -> f:format.raw()}

            <f:if condition="{image}">
                <my:atom.image image="{image}" class="teaser__image" />
            </f:if>
        </a>
    </fc:renderer>
</fc:component>
```

For further instructions on how to build components, please refer to the [documentation of Fluid Components](https://github.com/sitegeist/fluid-components).

## Adding fixtures to your component

Each component in the styleguide needs a fixture file which contains example values for all of the component's required arguments.
A fixture file must at least contain a `default` fixture, but it may define additional fixtures that can then be selected
in the styleguide interface.

A fixture can be created in `.json`, [`.json5`](https://json5.org/), `.yml` or `.yaml` files. You should create only one fixture file per
component. The styleguide takes the first fixture file and ignores eventually existing files in other formats in the
following order:

1. `.json`
2. `.json5`
3. `.yml`
4. `.yaml`

Support for `json5` only works if you install the fluid-styleguide with composer (not by TER!).

**Molecule/Teaser/Teaser.fixture.json:**

```json
{
    "default": {
        "title": "TYPO3",
        "link": "https://typo3.org"
    },
    "dark": {
        "title": "TYPO3",
        "link": "https://typo3.org",
        "theme": "dark"
    }
}
```

or **Molecule/Teaser/Teaser.fixture.json5:**

```json5
{
    // can contain comments
    "default": {
        "title": "TYPO3 \
can contain multiline strings",
        "link": "https://typo3.org"
    },
    "dark": {
        "title": 'TYPO3 with single quotes',
        "link": "https://typo3.org",
        "theme": "dark"
    }
}
```

or **Molecule/Teaser/Teaser.fixture.yaml:**

```yaml
default:
    title: |
       TYPO3
       Multiline
    link: https://typo3.org
dark:
    title: TYPO3
    link: https://typo3.org
    theme: dark
```

File naming scheme: *{ComponentName}.fixture.[json|[json5](https://json5.org/)|yml|yaml]*

You can define the content of a component if the component supports it:

```json
{
    "default": {
        "content": "The professional, flexible Content Management System"
    }
}
```

You can use fluid in your fixture data to nest components:

```json
{
    "default": {
        "actions": "<my:atom.button>Primary Button</my:atom.button><my:atom.button isSecondary='1'>Secondary Button</my:atom.button>"
    }
}
```

You can use [data structures with argument converters](https://github.com/sitegeist/fluid-components/blob/master/Documentation/DataStructures.md) to define placeholder images and advanced links:

```json
{
    "default": {
        "image": {
            "height": 300,
            "width": 500,
            "alternative": "My alt text",
            "title": "My image title"
        },
        "link": {
            "uri": "https://typo3.org",
            "target": "_blank"
        }
    }
}
```

You can provide a label for each fixture:

```json
    "onDarkBackground": {
        "styleguideFixtureLabel": "On dark background",
    },
```

You can override the default [component context](./ConfigurationReference.md) for each fixture:

```json
{
    "onDarkBackground": {
        "styleguideComponentContext": "<div class=\"myDarkBackground\">|</div>"
    }
}
```

## Adding documentation to your component

If you want to add further documentation to your component, just place a markdown file that is named like your component
inside your component folder. Fluid Styleguide will pick up the documentation automatically and render it in the DOC tab.

**Molecule/Teaser/Teaser.md:**

```markdown
## Teaser Component

This is a generic teaser components. It supports both a light and a dark styling. [...]
```

File naming scheme: *{ComponentName}.md*
