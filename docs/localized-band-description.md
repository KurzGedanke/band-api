# Localized band descriptions

Band descriptions are served in **two languages**: German (`de`, the default)
and English (`en`). German is the primary language — English is optional and may
be `null` for a given band.

## API shape

`description` is no longer a plain string. On every endpoint that returns a band
(`GET /api/festivals/{festivalSlug}/bands` and
`GET /api/festivals/{festivalSlug}/{bandSlug}`) it is a localized object:

```json
{
  "id": 8,
  "name": "Amorphis",
  "slug": "amorphis",
  "genre": "Melodic Death / Heavy Metal",
  "description": {
    "de": "Metal-Band aus Helsinki, Finnland, gegründet 1990 …",
    "en": "Metal band from Helsinki, Finland, formed in 1990 …"
  }
}
```

Either field may be `null`:

```json
"description": { "de": "Deutsche Beschreibung …", "en": null }
```

Both keys are always present in the response (the value is `null` when there is
no copy for that language). German is the default — show it unless the user
prefers English, and fall back to it when the preferred language is `null`.

> **Breaking change.** Previously `description` was a `string`. Clients that read
> it as a string must be updated to read `description.de` / `description.en`.

## iOS implementation (Swift)

A tiny reusable `Codable` type plus one computed property covers everything.

```swift
import Foundation

/// A piece of text the API provides in German (default) and English.
struct LocalizedText: Codable, Hashable {
    let de: String?
    let en: String?

    /// Text for the user's current language, German-first with a fallback to
    /// whichever language is actually present.
    var localized: String? {
        let prefersEnglish = Locale.current.language.languageCode?.identifier == "en"
        if prefersEnglish {
            return en ?? de          // English preferred, fall back to German
        }
        return de ?? en              // German default, fall back to English
    }
}

struct Band: Codable, Identifiable {
    let id: Int
    let name: String
    let slug: String
    let genre: String?
    let logo: String
    let image: String
    let instagram: String?
    let spotify: String?
    let appleMusic: String?
    let bandcamp: String?
    let description: LocalizedText
}
```

Usage in a view:

```swift
if let text = band.description.localized {
    Text(text)
}
```

### Letting the user override the language

If your app has an in-app language switch instead of relying on the device
locale, pass the preference in rather than reading `Locale.current`:

```swift
extension LocalizedText {
    func text(for language: AppLanguage) -> String? {
        switch language {
        case .german:  return de ?? en
        case .english: return en ?? de
        }
    }
}
```

That's the whole integration: decode `description` as `LocalizedText` and call
`localized` (or `text(for:)`) wherever you currently show the description.

## Data model & backend notes

- The `Band` entity stores two nullable `TEXT` columns: `description_de` (the
  default) and `description_en`.
- Migration `Version20260623120000` renames the legacy `description` column to
  `description_en` (it held English copy) and adds an empty `description_de`.
  Run it and then re-seed:

  ```bash
  php bin/console doctrine:migrations:migrate
  php bin/console app:seed:band-details --overwrite
  ```

- The EasyAdmin band form exposes both as **Description (DE)** and
  **Description (EN)** editors.
- To add a third language later, add a `description_xx` column + getter/setter,
  emit the key in `FestivalApiController`, and add the field to `LocalizedText`
  in `openapi.yaml`. No client breakage — new keys are additive.
