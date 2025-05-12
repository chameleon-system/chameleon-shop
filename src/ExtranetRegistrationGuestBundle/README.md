Chameleon System ExtranetRegistrationGuestBundle
================================================

## Overview
This bundle provides a "Register After Shopping" feature for the Chameleon Shop, allowing guests to convert their recent purchase session into a full extranet account.
It renders a customizable registration form on the thank-you page, pre-populated with the guest's last order context, and handles account creation seamlessly.

## Key Features
- Inserts a "Create Account" form on the shop's thank-you page (`register-after-shopping` system page).
- Pre-fills form mappings with guest order data and extranet configuration.
- Uses a dedicated Twig template for styling and layout (`formCreateAccountFromGuest.html.twig`).
- Abstracted form logic in `TPkgExtranetRegistrationGuestMapper_Form` for mapping requirements and error handling.
- Can be customized by overriding templates and mappers.

## Setup & Installation
1. **Copy the view**:
   ```bash
   cp -R vendor/chameleon-system/chameleon-shop/src/ExtranetRegistrationGuestBundle/installation/toCopy/framework/modules/MTExtranet \
      src/framework/modules/MTExtranet
   ```
   This adds `registerGuest.view.php` under `src/framework/modules/MTExtranet/`.
2. **Configure Extranet view**:
   - In the **Table Editor**, open `cms_tpl_module_instance`.
   - Add a new **Module Instance**:
     - **Module Type**: `MTExtranet`
     - **View Template**: `registerGuest`
     - Choose your **Portal** and **Theme**.
3. **Create a Frontend Page**:
   - In **Table Editor**, open `cms_tpl_page`.
   - Create a new page record for registration.
   - Add the `MTExtranet` module instance with view `registerGuest` to the page.
4. **Assign System Page**:
   - In **System Pages** (`cms_config` > `systemPageModule`), map the key `register-after-shopping` to your new page.
5. **Clear Cache**:
   ```bash
   php bin/console cache:clear
   ```

## Mapper Logic
The form behavior is driven by `TPkgExtranetRegistrationGuestMapper_Form`:
- **GetRequirements** declares source objects: active extranet user, extranet configuration, thank-you order step, and a text block.
- **Accept** checks if the current order step is `thankyou`, validates eligibility via `RegistrationGuestIsAllowed`, loads form error messages, and maps form fields (`password`, `password2`) and redirect URLs.

## Template Override
The default form template is `common/userInput/form/formCreateAccountFromGuest.html.twig`. Copy this file into your theme under:
```
src/themes/<YourTheme>/snippets-cms/common/userInput/form/formCreateAccountFromGuest.html.twig
```
and customize HTML/CSS as needed.

## Extensibility
- **Custom Mapper**: Extend `TPkgExtranetRegistrationGuestMapper_Form` to modify form logic, tag your subclass with `chameleon_system.mapper`, and update the module instance.
- **Additional Fields**: Add or remove form fields by editing the Twig template and mapper requirements.
- **Post-Registration Hook**: Listen to the extranet user creation event (`TdbDataExtranetUser` lifecycle hooks) to trigger notifications or integrations.

## License
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.