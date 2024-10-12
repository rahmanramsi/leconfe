import './alpine/components/tabs'
import './alpine/components/motion'
import './alpine/components/sidebarsManager'
import './alpine/components/navigationMenuItemSortable'
import { polyfillCountryFlagEmojis } from "country-flag-emoji-polyfill";

import.meta.glob(["../../assets/**"]);

polyfillCountryFlagEmojis();