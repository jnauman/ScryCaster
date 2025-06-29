# ScryCaster User Guide

## 1. Introduction

Welcome to ScryCaster!

ScryCaster is a powerful web application designed to help Game Masters (GMs) and players manage their tabletop role-playing game (TTRPG) adventures. Whether you're running an epic campaign or a thrilling one-shot, ScryCaster provides tools to streamline gameplay, organize information, and enhance your gaming experience.

Key features include:

*   **Campaign Management:** Create and organize your campaigns, complete with descriptions and unique join codes for your players.
*   **Character Management:** Develop detailed player characters with customizable stats, abilities, and backstories.
*   **Monster Management:** Build your bestiary with custom monsters or import them for quick setup.
*   **Encounter Management:** Design and run dynamic encounters with intuitive turn tracking, initiative calculation, and visual aids using campaign images.
*   **User Customization:** Personalize your experience with profile settings and appearance themes.
*   **Admin Panel:** A comprehensive Filament-based admin panel for advanced resource management.

This guide will walk you through the various features of ScryCaster, from getting started to mastering its advanced functionalities. Let's dive in!

## 2. Getting Started

This section will guide you through creating an account, logging in, and understanding the main dashboard.

### 2.1. Account Creation and Login

To start using ScryCaster, you'll need to create an account.

*   **Registration:**
    1.  Navigate to the ScryCaster homepage.
    2.  Click on the "Register" link, usually found in the top-right corner.
    3.  Fill out the registration form with your desired username, email address, and a secure password.
    4.  Follow any email verification steps if prompted.

*   **Login:**
    1.  Once your account is created, or if you're a returning user, click on the "Log in" link on the homepage.
    2.  Enter your registered email address and password.
    3.  You'll be redirected to your ScryCaster dashboard upon successful login.

*   **Password Reset:**
    1.  If you've forgotten your password, click on the "Forgot Password" link on the login page.
    2.  Enter your email address, and you'll receive instructions on how to reset your password.

### 2.2. Overview of the Dashboard

Upon logging in, you'll land on your **Dashboard**. This is your central hub for managing your TTRPG activities.

The dashboard typically displays:

*   **Welcome Message:** A greeting and a brief introduction to the dashboard.
*   **DM Dashboard (Admin Panel) Link:** A prominent link or button (often labeled "Go to Your DM Dashboard" or similar) that takes you to the Filament admin panel. This is where Game Masters will do most of their campaign and resource setup. We'll cover the Admin Panel in more detail in a later section.
*   **Active Encounters (Player View):** This section lists any encounters you are currently a part of, either as a GM or as a player character.
    *   Each encounter card will typically show the encounter name and the campaign it belongs to.
    *   You'll find a "Go to Player View" or "Go to Encounter" button for each, which takes you to the live, player-facing view of that encounter. This view is ideal for gameplay, showing turn order, maps (if applicable), and other real-time information.
*   **Navigation:** Links to other parts of the application, such as settings or your profile, are usually available in a navigation bar or user menu.

The dashboard is designed to give you quick access to your ongoing games and the tools you need to manage them. The exact layout might vary slightly, but these core elements will be present.

## 3. Campaign Management

Campaigns are the backbone of your adventures in ScryCaster. As a Game Master (GM), you'll create and manage campaigns primarily through the DM Dashboard (Admin Panel).

### 3.1. Accessing Campaign Management

1.  Log into ScryCaster.
2.  From your main dashboard, click on the "Go to Your DM Dashboard" (or similarly named) link. This will take you to the Filament admin panel.
3.  In the admin panel's navigation sidebar (usually on the left), find and click on "Campaigns". This will display a list of your existing campaigns.

### 3.2. Creating a New Campaign

1.  In the "Campaigns" section of the admin panel, look for a "New Campaign" or "+" button, typically located in the top-right corner. Click it.
2.  You'll be presented with a form to enter the campaign details:
    *   **Campaign Name:** A required field for the name of your campaign (e.g., "The Dragon's Hoard," "Curse of the Shadow Lord").
    *   **Description:** An optional field where you can add a more detailed description of your campaign, notes for yourself, or a summary for your players.
3.  The **Join Code** is automatically generated when you save the campaign. You won't need to fill this in during creation.
4.  The **Game Master (GM)** will be automatically assigned to you (the logged-in user).
5.  Once you've filled in the necessary information, click the "Create" or "Save" button. Your new campaign will be added to the list.

### 3.3. Viewing and Editing Campaign Details

1.  From the "Campaigns" list in the admin panel, click on the name of the campaign you wish to view or edit, or click the "Edit" action associated with it.
2.  This will open the campaign's detail page. Here you can:
    *   **View Details:** See all the information associated with the campaign.
    *   **Edit Details:** Modify the Campaign Name or Description. Click the "Save" or "Update" button after making changes.
    *   **Join Code:** The unique `join_code` for your campaign will be visible here. This code is read-only.
        *   **Copying the Join Code:** There's usually a "Copy" button or icon next to the join code field. Clicking this will copy the code to your clipboard, making it easy to share with your players.
    *   **Manage Related Resources:** On the campaign detail page, you'll often find sections or tabs for managing related items, such as:
        *   **Characters:** View, add, or remove player characters associated with this campaign.
        *   **Encounters:** Create new encounters for this campaign or manage existing ones.
        *   **Campaign Images:** Upload and manage images specific to this campaign (e.g., maps, NPC portraits).

### 3.4. Inviting Players (Using the Join Code)

While ScryCaster's current structure (based on the provided files) focuses on GM-centric campaign setup, the `join_code` suggests a mechanism for players to associate with a campaign. The exact player-side interface for using this code isn't detailed in the current file set, but the GM's role is to:

1.  Create the campaign and note its `join_code`.
2.  Share this `join_code` with their players.
3.  Players would then (presumably through a yet-to-be-detailed interface or by informing the GM) use this code to link their characters to the campaign. The GM might then see these characters appear in the "Characters" relation manager for the campaign.

*(Note: The exact player experience for joining a campaign might need further exploration if player-specific views or actions are implemented beyond character association by the GM).*

### 3.5. Deleting a Campaign

1.  In the "Campaigns" list within the admin panel:
    *   **Bulk Delete:** You can select multiple campaigns using checkboxes and then use a "Delete Selected" or similar bulk action.
    *   **Individual Delete:** Some interfaces might offer a "Delete" action directly on each campaign row, or you might need to go into the "Edit" page for a campaign to find a "Delete" button.
2.  Confirm the deletion when prompted. Be careful, as deleting a campaign is usually permanent and may also affect associated encounters or other linked data.

## 4. Character Management

In ScryCaster, "Characters" primarily refer to Player Characters (PCs). These are the heroes controlled by your players (or yourself, if you're testing or running solo). Monster management is handled separately. Characters are typically managed within the DM Dashboard (Admin Panel).

### 4.1. Accessing Character Management

1.  Log into ScryCaster and navigate to the DM Dashboard (Admin Panel).
2.  In the admin panel's navigation sidebar, click on "Characters". This will show a list of all characters you own.

    *Alternatively, you can manage characters specific to a campaign:*
    1.  Navigate to "Campaigns" in the admin panel.
    2.  Select the campaign you want to manage characters for.
    3.  Within the campaign's edit page, look for a "Characters" tab or relation manager section.

### 4.2. Creating a New Character

1.  In the "Characters" section of the admin panel, click the "New Character" or "+" button.
2.  You'll be presented with a form to define the character. There are two main ways to input data:

    *   **Manual Entry:** Fill in the fields directly:
        *   **Character Name:** (Required) The name of the character.
        *   **Armor Class (AC):** (Required) The character's defensive value.
        *   **Strength, Dexterity, Constitution, Intelligence, Wisdom, Charisma:** The character's core ability scores (numeric, defaults to 10 if not specified).
        *   **Max Health:** (Required) The character's maximum hit points (numeric, defaults to 10).
        *   **Class:** The character's class (e.g., Fighter, Wizard).
        *   **Ancestry:** The character's ancestry or race (e.g., Elf, Dwarf).
        *   **Title:** An optional title for the character.
        *   **Character Image:** Upload an image for the character (e.g., portrait).

    *   **JSON File Upload:**
        *   **Character JSON File:** You can upload a JSON file containing character data. ScryCaster will attempt to parse this file and populate the fields automatically. The expected JSON structure includes keys like `name`, `armorClass`, `maxHitPoints`, and a `stats` object for ability scores (e.g., `stats: {"STR": 14, "DEX": 12, ...}`).
        *   If the JSON is parsed successfully, the relevant fields in the form will be filled. The full original JSON data is also stored.
        *   If the JSON is invalid or doesn't contain the expected fields, the form fields might remain blank or be cleared.

3.  The **Owner (GM)** of the character is automatically set to your user account.
4.  After filling in the details (manually or via JSON upload), click "Create" or "Save".

### 4.3. Viewing and Editing Character Details

1.  From the "Characters" list in the admin panel, click on a character's name or its "Edit" action.
2.  This opens the character's detail page, where you can:
    *   Modify any of the fields described in the creation section (Name, AC, stats, image, etc.).
    *   Upload a new JSON file to update the character's data.
3.  Click "Save" or "Update" to apply your changes.

### 4.4. Linking Characters to Campaigns

Characters need to be associated with a campaign to participate in its encounters.

1.  Navigate to the specific **Campaign** in the admin panel.
2.  Open the campaign for editing.
3.  Find the **Characters** relation manager (it might be a tab or a section on the page).
4.  Here, you can:
    *   **Attach Existing Characters:** There will typically be an "Attach" or "Add" button. This will let you search for and select existing characters you own to add them to the campaign.
    *   **Create and Attach New Character:** Some interfaces might allow you to create a new character directly from this view, automatically linking it to the current campaign.
    *   **Detach Characters:** Remove characters from the campaign (this usually doesn't delete the character itself, just its association with that campaign).

### 4.5. Deleting a Character

1.  In the "Characters" list within the admin panel:
    *   **Bulk Delete:** Select multiple characters and use a "Delete Selected" bulk action.
    *   **Individual Delete:** Click the "Delete" action for a specific character.
2.  Confirm the deletion. Deleting a character is permanent and will remove it from any campaigns or encounters it was part of.

## 5. Monster Management

ScryCaster allows Game Masters to create a bestiary of monsters to use in their encounters. Monsters can be created manually, imported from JSON files (individually or in bulk), and are managed through the DM Dashboard (Admin Panel).

### 5.1. Accessing Monster Management

1.  Log into ScryCaster and go to the DM Dashboard (Admin Panel).
2.  In the admin panel's navigation sidebar, click on "Monsters". This will display a list of all available monsters.

### 5.2. Creating a New Monster

1.  In the "Monsters" section of the admin panel, click the "New Monster" or "+" button.
2.  A form will appear for you to define the monster's attributes:

    *   **Upload Monster JSON (Optional):**
        *   You can upload a JSON file to pre-fill monster data. The system will attempt to parse this file.
        *   Manually entered data in the fields below will override any values from the JSON if edited after the upload.
        *   *(Note: The exact parsing logic for individual monster JSON via this field in `MonsterResource` might be a placeholder or less extensive than the bulk importer. Refer to bulk import for detailed JSON structure expectations).*

    *   **Name:** (Required) The name of the monster (e.g., "Goblin Archer," "Young Red Dragon").
    *   **Armor Class (AC):** (Required) The monster's defensive value (numeric).
    *   **Max Health:** The monster's maximum hit points (numeric, defaults to 10).
    *   **Strength, Dexterity, Constitution, Intelligence, Wisdom, Charisma:** The monster's core ability scores (numeric, default to 10).
    *   **Additional Data (JSON):** A text area where you can input any other monster details as a JSON object (e.g., special abilities, resistances, vulnerabilities, actions). This provides flexibility for storing complex data.
        *   The `Monster` model also has specific fields like `slug`, `description`, `armor_type`, `attacks`, `movement`, `alignment`, `level`, and `traits` which are primarily targeted by the bulk importer. When creating manually, simpler stats go into the direct fields, and more complex or custom data can go into the "Additional Data (JSON)" field or be structured if the form is extended.
    *   **Owner (GM):**
        *   This field allows you to assign ownership of the monster.
        *   By default, it's set to your user account (the logged-in GM).
        *   You can also choose "Global Monster (Shared)" (or leave it blank/null) to make the monster available to all GMs (if the system supports this concept broadly).

3.  After filling in the details, click "Create" or "Save".

### 5.3. Bulk Importing Monsters (JSON)

ScryCaster offers a powerful bulk import feature for adding multiple monsters at once using a single JSON file. This is often accessed via a button or action within the "Monsters" list page in the admin panel (e.g., "Bulk Import Monsters").

1.  **Prepare your JSON file:**
    *   The JSON file should contain an array of monster objects.
    *   Each monster object should have fields corresponding to the monster's attributes. Key fields include:
        *   `name` (required, string)
        *   `slug` (required, string, must be unique across all monsters)
        *   `description` (string, nullable)
        *   `armor_class` or `ac` (integer, nullable)
        *   `armor_type` (string, nullable)
        *   `hit_points` or `max_health` (integer, nullable)
        *   `attacks` (string, nullable)
        *   `movement` (string, nullable)
        *   `strength`, `dexterity`, `constitution`, `intelligence`, `wisdom`, `charisma` (integer, nullable)
        *   `alignment` (string, nullable)
        *   `level` (integer, nullable)
        *   `traits` (array of objects, nullable). Each trait object should have:
            *   `name` (required if traits exist, string)
            *   `description` (required if traits exist, string)
    *   Example of a single monster entry in the JSON array:
        ```json
        {
          "name": "Goblin Scout",
          "slug": "goblin-scout",
          "ac": 13,
          "armor_type": "leather armor",
          "max_health": 7,
          "strength": 8,
          "dexterity": 14,
          "constitution": 10,
          "intelligence": 10,
          "wisdom": 8,
          "charisma": 8,
          "alignment": "neutral evil",
          "level": 1,
          "traits": [
            { "name": "Nimble Escape", "description": "The goblin can take the Disengage or Hide action as a bonus action on each of its turns." }
          ]
        }
        ```

2.  **Upload the JSON file:**
    *   In the "Monsters" section of the admin panel, find the "Bulk Import Monsters" action/button. This will likely open a modal or a dedicated page.
    *   Select your prepared JSON file using the file input.
    *   Click "Save" or "Import".

3.  **Processing and Feedback:**
    *   The system will validate each monster entry in the JSON file.
    *   If all entries are valid, the monsters will be created. You'll receive a success message indicating how many monsters were imported.
    *   If there are errors (e.g., missing required fields, duplicate slugs, invalid data types), the import will typically be rolled back (no monsters created from that file), and you'll receive an error message detailing the issues found and for which monster entries.
    *   Check for success or error messages, often displayed as notifications or flashes.

### 5.4. Viewing and Editing Monster Details

1.  From the "Monsters" list in the admin panel, click on a monster's name or its "Edit" action.
2.  This opens the monster's detail page, where you can modify any of its attributes as defined during creation.
3.  Click "Save" or "Update" to apply changes.

### 5.5. Deleting a Monster

1.  In the "Monsters" list:
    *   **Bulk Delete:** Select multiple monsters and use a "Delete Selected" bulk action.
    *   **Individual Delete:** Click the "Delete" action for a specific monster.
2.  Confirm the deletion. Deleting a monster is permanent. It will not affect past encounters where instances of this monster type were used, but you won't be able to add new instances of this monster type to encounters unless you recreate it.

## 6. Encounter Management

Encounters are specific scenes or challenges within your campaigns, often involving combat. ScryCaster provides tools to set up encounters, add combatants, and run them with turn tracking and visual aids. Encounters are managed by the GM through the DM Dashboard (Admin Panel), with a separate player-facing view for active gameplay.

### 6.1. Accessing Encounter Management

1.  Log into ScryCaster and navigate to the DM Dashboard (Admin Panel).
2.  You can manage encounters in two main ways:
    *   **Globally:** Click on "Encounters" in the admin panel's navigation sidebar. This lists all encounters across your campaigns.
    *   **Per Campaign:**
        1.  Navigate to "Campaigns" and select the campaign you want to manage encounters for.
        2.  Within the campaign's edit page, find the "Encounters" tab or relation manager section. This will list encounters specific to that campaign.

### 6.2. Creating a New Encounter

It's generally recommended to create encounters from within a specific campaign:

1.  Navigate to the desired **Campaign** in the admin panel and open its edit page.
2.  Go to the "Encounters" relation manager section.
3.  Click the "New Encounter" or "+" button.
4.  A form will appear:
    *   **Campaign:** This will usually be pre-filled with the current campaign. If creating globally, you'll need to select a campaign owned by you.
    *   **Encounter Name:** (Required) A descriptive name for the encounter (e.g., "Goblin Ambush," "The Lich's Lair").
    *   *Initial `current_round` and `current_turn` are typically set to 0 or 1 automatically by the system when the encounter starts.*
5.  Click "Create" or "Save". The new encounter will be listed.

### 6.3. Setting Up an Encounter (Adding Combatants)

Once an encounter is created, you need to add Player Characters (PCs) and Monster Instances.

1.  From the encounters list (either global or within a campaign), click "Edit" for the encounter you want to set up, or click its name.
2.  This opens the encounter's detail page in the admin panel. Here you'll find relation managers for:
    *   **Player Characters:**
        *   Click "Attach" or "Add Player Character".
        *   Select from a list of characters associated with the encounter's campaign.
        *   For each attached PC, you'll need to set their **Initiative Roll**. This is crucial for determining turn order.
    *   **Monster Instances:**
        *   Click "New Monster Instance" or "Add Monster".
        *   **Select Monster Type:** Choose a base monster from your bestiary.
        *   **Initiative Roll:** Set the initiative for this specific monster instance.
        *   **(Optional) Current Health / Max Health:** You can pre-set health if it differs from the base monster's default, or track it during the encounter. Max health is often copied from the base monster.
        *   You can add multiple instances of the same monster type or different monster types. Each will be a separate combatant.

### 6.4. Running an Encounter ("Run" Page - GM View)

The "Run Encounter" page is the GM's command center for managing an active encounter.

1.  Accessing the "Run" page:
    *   From the encounter list (global or campaign-specific), find the encounter and click its "Run" action/button (often a play icon).
    *   Alternatively, if you're editing an encounter, there might be a link/button to go to the "Run" page.
    *   The URL will typically be something like `/admin/encounters/{id}/run`.

2.  **Key Features of the "Run" Page:**
    *   **Combatant List:** Displays all PCs and monster instances in initiative order.
        *   The current turn's combatant is usually highlighted.
        *   GMs can often edit initiative rolls or health directly from this list.
    *   **Turn Controls:**
        *   **Calculate Order/Start Encounter:** An action to calculate the initial turn order based on initiative rolls (and dexterity for ties). This also usually sets the round to 1 and turn to 1.
        *   **Next Turn:** Advances to the next combatant in the order. If it's the last combatant's turn, it wraps around to the first combatant and increments the round counter.
        *   *(Specific controls for managing health, conditions, etc., might vary based on implementation details not fully visible in the provided files, but are common in such systems).*
    *   **Campaign Image Display:**
        *   GMs can select a **Campaign Image** (e.g., a map or scene art) to be displayed. These images are typically uploaded and managed within the Campaign settings.
        *   Changing the selected image here will update what players see on the Player-Facing Encounter Dashboard.
    *   **Round and Turn Display:** Clearly shows the current round number and whose turn it is.

### 6.5. Player-Facing Encounter Dashboard

This is the view your players will see. It's a simplified display focused on the current state of the encounter.

1.  **Accessing:**
    *   From the main ScryCaster dashboard (after logging in), users will see a list of their "Active Encounters."
    *   Clicking "Go to Player View" or "Go to Encounter" for a specific encounter will take them to its player-facing dashboard.
    *   The URL is typically `/encounter/{id}`.

2.  **Key Features:**
    *   **Encounter Name and Round:** Displays the current encounter and round.
    *   **Turn Order List:** Shows all combatants in order.
        *   The combatant whose turn it is is prominently highlighted.
        *   Includes combatant names, images (if available), and type (Player/Monster).
    *   **Encounter Image:** Displays the image selected by the GM on the "Run Encounter" page.
    *   **Real-time Updates:** This dashboard listens for events broadcast by the server (using Laravel Echo). When the GM:
        *   Advances the turn (`TurnChanged` event), the highlighted combatant and round number update automatically for all players.
        *   Changes the displayed image (`EncounterImageUpdated` event), the image updates automatically.

### 6.6. Editing and Deleting Encounters

*   **Editing:**
    1.  Navigate to the encounter list in the admin panel.
    2.  Click "Edit" for the desired encounter.
    3.  You can change its name, and manage attached player characters and monster instances (including their initiative, health for monsters, etc.).
*   **Deleting:**
    1.  From the encounter list, click "Delete" for the encounter you want to remove.
    2.  Confirm the deletion. This will remove the encounter and its associated combatant links.

*(Note: Managing status effects, detailed health tracking for PCs, or specific actions for combatants within the "Run Encounter" page would depend on further UI elements and backend logic beyond what's detailed in the current file set, but the foundation for turn and image management is present).*

## 7. User Settings

ScryCaster allows you to customize your account information and application appearance through the User Settings area.

### 7.1. Accessing User Settings

1.  Log into your ScryCaster account.
2.  Look for a "Settings" link or a user profile icon/menu, usually located in the header or a navigation bar. Clicking this will take you to the settings area.
3.  The settings are typically organized into sections like Profile, Password, and Appearance. You may be redirected to the "Profile" section by default.

### 7.2. Profile Settings

The Profile settings page allows you to manage your basic account information.

*   **Navigate:** If not already there, select the "Profile" tab or link within the settings area.
*   **Fields:**
    *   **Name:** Update your display name.
    *   **Email:** Change your registered email address.
        *   If you change your email, you may need to re-verify it. An email verification link will be sent to the new address.
*   **Updating:**
    1.  Modify the desired fields.
    2.  Click the "Save" button. You should see a confirmation message (e.g., "Saved.").
*   **Email Verification:**
    *   If your email address is unverified, a message will be displayed.
    *   You can click a link to "re-send the verification email."
    *   A confirmation that "A new verification link has been sent" will appear. Check your email inbox for the verification link.
*   **Delete Account:**
    *   This section also typically contains a "Delete Account" form or button. Be very careful with this option, as deleting your account is permanent and will erase all your data, including campaigns, characters, and monsters. You'll likely need to confirm your password to proceed with account deletion.

### 7.3. Password Settings

Secure your account by updating your password regularly.

*   **Navigate:** Select the "Password" tab or link within the settings area.
*   **Fields:**
    *   **Current password:** Enter your existing password for verification.
    *   **New password:** Enter your desired new password.
    *   **Confirm Password:** Re-enter the new password to ensure accuracy.
*   **Updating:**
    1.  Fill in all three fields. Ensure your new password meets any displayed security requirements (e.g., length, character types).
    2.  Click the "Save" button.
    3.  If successful, your password will be updated, and you'll see a confirmation message. If there's an error (e.g., current password incorrect, new passwords don't match), an error message will be shown.

### 7.4. Appearance Settings

Customize the look and feel of ScryCaster to your preference.

*   **Navigate:** Select the "Appearance" tab or link within the settings area.
*   **Options:**
    *   **Mode (Light/Dark):**
        *   Choose between "Light" mode, "Dark" mode, or "System" (which follows your operating system's current theme setting).
    *   **Color Theme:**
        *   Select from a list of available color accents or themes (e.g., "Havelock Blue," "Earthen & Arcane"). This changes the primary accent colors used throughout the application.
*   **Applying Changes:**
    *   Changes to appearance settings are usually applied immediately as you select them. There might not be a separate "Save" button for these options.

## 8. DM Dashboard (Filament Admin Panel)

The DM Dashboard, powered by Filament, is the primary interface for Game Masters (GMs) to create, manage, and organize all aspects of their ScryCaster games. It provides a structured and efficient way to handle campaigns, characters, monsters, and encounters.

### 8.1. Accessing the DM Dashboard

1.  Log into ScryCaster.
2.  On your main user dashboard, click the prominent link or button that says "Go to Your DM Dashboard," "Admin Panel," or similar. This will typically redirect you to the `/admin` path.

### 8.2. Overview of the Interface

The Filament admin panel generally consists of:

*   **Navigation Sidebar:** Usually on the left side, this lists all the manageable "Resources" (like Campaigns, Characters, Monsters, Encounters). Clicking on a resource name will take you to its main listing page.
*   **Main Content Area:** This area displays the list of items for the selected resource, forms for creating/editing items, or detail pages.
*   **User Menu:** Often in the top-right, providing access to your account (e.g., logout).
*   **Action Buttons:**
    *   **Create New:** Typically a button like "New [Resource Name]" (e.g., "New Campaign") found on list pages, usually in the top-right.
    *   **Edit/Delete/View:** Actions available for each item in a list (often as buttons or icons in a row).
    *   **Bulk Actions:** Options to perform actions (like delete) on multiple selected items.

### 8.3. Key Resources and Their Management

As detailed in previous sections, the DM Dashboard is where you'll perform most setup and management tasks:

*   **Campaigns:**
    *   Create new campaigns with names and descriptions.
    *   View and copy the auto-generated `join_code` to share with players.
    *   Edit campaign details.
    *   Manage associated Characters, Encounters, and Campaign Images through "Relation Managers" on the campaign's edit page.
*   **Characters (Player Characters):**
    *   Create new player characters, either by manual data entry or by uploading a JSON file.
    *   Edit character stats, details, and images.
    *   Characters are owned by the GM who creates them.
*   **Monsters:**
    *   Create individual monsters with their stats and abilities.
    *   Utilize the "Bulk Import Monsters" feature to add many monsters at once from a structured JSON file.
    *   Edit existing monsters.
    *   Monsters can be owned by a specific GM or be "Global" (shared).
*   **Encounters:**
    *   Create encounters, linking them to one of your campaigns.
    *   Add Player Characters (from the campaign) and Monster Instances (from your bestiary) to the encounter.
    *   Set initiative rolls for all combatants.
    *   Access the dedicated "Run Encounter" page for active encounter management (turn tracking, image display).

### 8.4. Common Patterns in the Admin Panel

*   **Resource Listing Pages:**
    *   Display items in a table format.
    *   Columns are often sortable and searchable.
    *   Provide actions for each item (Edit, Delete, custom actions like "Run" for encounters).
*   **Create/Edit Forms:**
    *   Provide fields for all necessary data.
    *   Use various input types (text fields, number inputs, dropdowns, file uploads).
    *   Often organized into one or more columns for better layout.
*   **Relation Managers:**
    *   Found on the edit pages of parent resources (e.g., on a Campaign's edit page, you'll find relation managers for its Characters and Encounters).
    *   Allow you to view, attach, detach, create, and sometimes edit related items directly within the context of the parent item. For example, adding characters to a campaign or adding monsters to an encounter.
*   **Filtering and Querying:**
    *   Many resources (like Campaigns) are automatically filtered to show only items owned by or relevant to the logged-in GM.

The DM Dashboard is your comprehensive toolkit for preparing and running your TTRPG sessions with ScryCaster. Familiarizing yourself with its layout and the resource management pages will greatly enhance your experience.

## 9. Troubleshooting / FAQ

Here are some common questions and troubleshooting tips for ScryCaster:

**Q: I can't see my Campaigns/Characters/Monsters in the DM Dashboard.**
*   **A:** Ensure you are logged in with the correct Game Master account that owns these resources. Most resources in the DM Dashboard are filtered to show only items belonging to the logged-in user. If a monster was created as "Global," it should be visible to all GMs.

**Q: My players can't see the encounter image or turn updates on their dashboard.**
*   **A:**
    1.  **GM Actions:** Make sure you, as the GM, are actively managing the encounter from the "Run Encounter" page in the DM Dashboard. Image changes and turn advancements are triggered by your actions there.
    2.  **Internet Connection:** Both GM and players need a stable internet connection for real-time updates (which use Laravel Echo/websockets).
    3.  **Correct Encounter:** Ensure players are viewing the correct encounter dashboard. They should access it via their main ScryCaster dashboard.
    4.  **Broadcasting Service:** The server's broadcasting service (e.g., Laravel WebSockets, Pusher) must be correctly configured and running. (This is more of a server admin note).

**Q: Bulk import for monsters failed. What should I check?**
*   **A:**
    1.  **JSON Format:** Ensure your file is a valid JSON array of monster objects. Validate the JSON structure using an online tool if unsure.
    2.  **Required Fields:** Each monster object must have a unique `slug` and a `name`. Check the "Monster Management" section for other expected fields like `ac`, `max_health`, etc.
    3.  **Error Messages:** The bulk importer should provide error messages indicating which monster entries failed and why. Review these messages carefully. Common issues include duplicate slugs or missing required data.
    4.  **File Size:** While generous, there's a max file size for uploads (e.g., 10MB).

**Q: I uploaded a character/monster JSON, but the fields didn't populate correctly.**
*   **A:** The JSON parser expects specific field names (e.g., `armorClass` not `AC`, `maxHitPoints` not `max_health` for some character JSON sources). If using a generic JSON, you might need to adjust its keys to match what ScryCaster's parser expects or enter the data manually. The system tries to map common variations but might not cover all formats.

**Q: How do I invite players to my campaign?**
*   **A:** When you create or edit a campaign in the DM Dashboard, a unique "Join Code" is generated. Share this code with your players. The exact mechanism for players to *use* this code to join isn't fully detailed from the GM-side files, but they would typically enter this code somewhere in their interface, or you might manually associate their characters with your campaign using the "Characters" relation manager in your campaign settings.

**Q: Can I change a character's initiative roll after an encounter has started?**
*   **A:** Yes, typically on the "Run Encounter" page (GM view), you should be able to edit the initiative value for any combatant, even after the order has been calculated. After changing it, you might need to re-trigger the "Calculate Order" or "Start Encounter" action to update the turn sequence.

**Q: What happens if I delete a Campaign/Monster/Character?**
*   **A:** Deleting these items is generally permanent.
    *   **Campaign:** Deleting a campaign will likely also remove its associated encounters and links to characters.
    *   **Monster (Type):** Deleting a monster type from your bestiary means you can't add new instances of it to encounters. It usually doesn't remove existing monster instances already placed in encounters.
    *   **Character:** Deleting a character removes it from your account and any campaigns/encounters it was part of.

If you encounter issues not covered here, consider checking any application logs (if accessible) or reaching out to the application administrator or support channel if available.

## 10. Glossary

*   **TTRPG (Tabletop Role-Playing Game):** A game where participants assume the roles of characters in a fictional setting. Gameplay progresses through social interaction, dice rolls, and a set of rules, often guided by a Game Master.
*   **GM (Game Master):** The person who runs the game, describes the world, controls non-player characters (NPCs) and monsters, and referees the rules. Also known as DM (Dungeon Master) in Dungeons & Dragons.
*   **PC (Player Character):** A character in the game controlled by one of the players (not the GM).
*   **NPC (Non-Player Character):** A character controlled by the GM.
*   **Campaign:** A series of connected adventures or a long-running storyline in a TTRPG.
*   **Encounter:** A specific scene, challenge, or interaction within a campaign, often involving combat or significant choices.
*   **Initiative:** A mechanic (usually involving a dice roll) to determine the order of turns in combat. Higher initiative generally means acting earlier.
*   **AC (Armor Class):** A value representing how difficult it is to hit a character or monster in combat.
*   **HP (Hit Points):** A measure of a character's or monster's health or vitality. Reaching 0 HP usually means being knocked out or killed.
*   **Stats / Ability Scores:** Numerical values representing a character's core attributes (e.g., Strength, Dexterity, Constitution, Intelligence, Wisdom, Charisma).
*   **Bestiary:** A collection or list of monsters, often with their stats and abilities.
*   **Slug (in Monster Management):** A unique, URL-friendly identifier for a monster (e.g., "red-dragon" instead of "Red Dragon"). Used in the bulk importer.
*   **Round (in Combat):** A full cycle in which every combatant gets a chance to take a turn.
*   **Turn (in Combat):** An individual combatant's opportunity to act during a round.
