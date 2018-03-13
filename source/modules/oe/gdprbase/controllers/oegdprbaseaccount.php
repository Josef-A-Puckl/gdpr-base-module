<?php
/**
 * This file is part of OXID eSales GDPR base module.
 *
 * OXID eSales GDPR base module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales GDPR base module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales GDPR base module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

/**
 * Class oeGdprBaseAccount.
 * Extends Account.
 *
 * @see Account
 */
class oeGdprBaseAccount extends oeGdprBaseAccount_parent
{
    private $oeGdprBaseIsUserDeleted = false;

    /**
     * Deletes User account.
     */
    public function oeGdprBaseDeleteAccount()
    {
        if ($this->oeGdprBaseCanBeUserAccountDeleted()) {
            $user = $this->getUser();
            $user->delete();
            $user->logout();
            $session = $this->getSession();
            $session->destroy();
            $this->oeGdprBaseIsUserDeleted = true;
        } else {
            oxRegistry::get("oxUtilsView")->addErrorToDisplay('OESDGVOBASE_ERROR_ACCOUNT_NOT_DELETED');
        }
    }

    /**
     * Method used to show message in frontend if user was successfully deleted.
     *
     * @return bool
     */
    public function oeGdprBaseIsUserDeleted()
    {
        return $this->oeGdprBaseIsUserDeleted;
    }

    /**
     * Returns true if User is allowed to delete own account.
     *
     * @return bool
     */
    public function oeGdprBaseIsUserAllowedToDeleteOwnAccount()
    {
        $allowUsersToDeleteTheirAccount = $this
            ->getConfig()
            ->getConfigParam('blOeGdprBaseAllowUsersToDeleteTheirAccount');

        $user = $this->getUser();

        return $allowUsersToDeleteTheirAccount && $user && !$user->oeGdprBaseIsMallAdmin();
    }

    /**
     * Checks if possible to delete user.
     *
     * @return bool
     */
    protected function oeGdprBaseCanBeUserAccountDeleted()
    {
        return $this->getSession()->checkSessionChallenge() && $this->oeGdprBaseIsUserAllowedToDeleteOwnAccount();
    }
}