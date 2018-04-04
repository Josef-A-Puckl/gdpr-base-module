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
 * Class oeGdprBaseAccountReviewController
 *
 * Extends oxUBase.
 *
 * @see oxUBase
 */
class oeGdprBaseAccountReviewController extends oxUBase
{
    /**
     * Controller template name.
     *
     * @var string
     */
    protected $_sThisTemplate = 'oegdprbaseaccountreviewcontroller.tpl';

    /**
     * Page navigation
     *
     * @var object
     */
    protected $_oPageNavigation = null;

    /**
     * @var int Items to be displayed per page
     */
    protected $oeGdprBaseItemsPerPage = 10;

    /**
     * Number of possible pages.
     *
     * @var integer
     */
    protected $_iCntPages;

    /**
     * Redirect to My Account, if validation does not pass.
     *
     * @throws oxSystemComponentException if a class name cannot be resolved
     */
    public function init()
    {
        if (!$this->oeGdprBaseIsUserAllowedToManageOwnReviews() || !$this->getUser()) {
            $this->oeGdprBaseRedirectToAccountDashboard();
        }

        parent::init();
    }

    /**
     * Returns Bread Crumb - you are here page1/page2/page3...
     *
     * @throws oxSystemComponentException if a class name cannot be resolved
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        return array (
            array (
                'title' => $this->oeGdprBaseGetTranslatedString('MY_ACCOUNT'),
                'link'  => $this->oeGdprBaseGetMyAccountPageUrl(),
            ),
            array (
                'title' => $this->oeGdprBaseGetTranslatedString('MY_REVIEWS'),
                'link'  => $this->getLink(),
            ),
        );
    }

    /**
     * Generates the pagination.
     *
     * @return \stdClass
     */
    public function getPageNavigation()
    {
        $this->_iCntPages = $this->oeGdprBaseGetPagesCount();
        $this->_oPageNavigation = $this->generatePageNavigation();

        return $this->_oPageNavigation;
    }

    /**
     * Return how many items will be displayed per page.
     *
     * @return int
     */
    public function oeGdprBaseGetItemsPerPage()
    {
        return $this->oeGdprBaseItemsPerPage;
    }

    /**
     * Get actual page number.
     *
     * @return int
     */
    public function getActPage()
    {
        $lastPage = $this->oeGdprBaseGetPagesCount();
        $currentPage = parent::getActPage();

        if ($currentPage >= $lastPage) {
            $currentPage = $lastPage - 1;
        }

        return $currentPage;
    }

    /**
     * Delete review and rating, which belongs to the active user.
     *
     * @throws oeGdprBaseEntryDoesNotExistDaoException if a review or a rating does not exist
     * @throws oeGdprBaseRatingPermissionException if the user has no permission to delete that rating
     * @throws oeGdprBaseReviewPermissionException if the user has no permission to delete that review
     */
    public function oeGdprBaseDeleteReviewAndRating()
    {
        if ($this->getSession()->checkSessionChallenge()) {
            try {
                $this->oeGdprBaseDeleteReview();
                $this->oeGdprBaseDeleteRating();
            } catch (oeGdprBaseEntryDoesNotExistDaoException $exception) {
                //if user reloads the page after deletion
            }
        }
    }

    /**
     * Returns Review List
     *
     * @throws oxSystemComponentException if a class name cannot be resolved
     *
     * @return array
     */
    public function oeGdprBaseGetReviewList()
    {
        $currentPage = $this->getActPage();
        $itemsPerPage = $this->oeGdprBaseGetItemsPerPage();
        $offset = $currentPage * $itemsPerPage;

        $userId = $this->getUser()->getId();

        $reviewModel = oxNew('oxReview');
        $reviewAndRatingList = $reviewModel->oeGdprBaseGetReviewAndRatingListByUserId($userId);

        return $this->oeGdprBaseGetPaginatedReviewAndRatingList(
            $reviewAndRatingList,
            $itemsPerPage,
            $offset
        );
    }

    /**
     * Get the total number of reviews for the active user.
     *
     * @return integer Number of reviews
     */
    public function oeGdprBaseGetReviewAndRatingItemsCount()
    {
        $user = $this->getUser();
        $count = 0;
        if ($user) {
            $count = $this
                ->oeGdprBaseGetContainer()
                ->getUserReviewAndRatingBridge()
                ->getReviewAndRatingListCount($user->getId());
        }

        return $count;
    }

    /**
     * Return true, if the review manager should be shown.
     *
     * @return bool
     */
    public function oeGdprBaseIsUserAllowedToManageOwnReviews()
    {
        return (bool) $this
            ->getConfig()
            ->getConfigParam('blOeGdprBaseAllowUsersToManageReviews');
    }

    /**
     * Deletes Review.
     *
     * @throws oeGdprBaseEntryDoesNotExistDaoException if a review or a rating does not exist
     * @throws oeGdprBaseReviewPermissionException if the user has no permission to delete that review
     */
    private function oeGdprBaseDeleteReview()
    {
        $userId = $this->getUser()->getId();
        $reviewId = $this->oeGdprBaseGetReviewIdFromRequest();

        if ($reviewId) {
            $userReviewBridge = $this->oeGdprBaseGetContainer()->getUserReviewBridge();
            $userReviewBridge->deleteReview($userId, $reviewId);
        }
    }

    /**
     * Deletes Rating.
     *
     * @throws oeGdprBaseEntryDoesNotExistDaoException if a review or a rating does not exist
     * @throws oeGdprBaseRatingPermissionException if the user has no permission to delete that rating
     */
    private function oeGdprBaseDeleteRating()
    {
        $userId = $this->getUser()->getId();
        $ratingId = $this->oeGdprBaseGetRatingIdFromRequest();

        if ($ratingId) {
            $userRatingBridge = $this->oeGdprBaseGetContainer()->getUserRatingBridge();
            $userRatingBridge->deleteRating($userId, $ratingId);
        }
    }

    /**
     * Retrieve the Review id from the request
     *
     * @return string
     */
    private function oeGdprBaseGetReviewIdFromRequest()
    {
        return oxRegistry::getConfig()->getRequestParameter('reviewId');
    }

    /**
     * Retrieve the Rating id from the request
     *
     * @return string
     */
    private function oeGdprBaseGetRatingIdFromRequest()
    {
        return oxRegistry::getConfig()->getRequestParameter('ratingId');
    }

    /**
     * Redirect to My Account dashboard
     *
     * @throws oxSystemComponentException if a class name cannot be resolved
     */
    private function oeGdprBaseRedirectToAccountDashboard()
    {
        oxRegistry::getUtils()->redirect(
            $this->oeGdprBaseGetMyAccountPageUrl(),
            true,
            302
        );
    }

    /**
     * Returns pages count.
     *
     * @return int
     */
    private function oeGdprBaseGetPagesCount()
    {
        return ceil($this->oeGdprBaseGetReviewAndRatingItemsCount() / $this->oeGdprBaseGetItemsPerPage());
    }

    /**
     * Returns My Account page url.
     *
     * @throws oxSystemComponentException if a class name cannot be resolved
     *
     * @return string
     */
    private function oeGdprBaseGetMyAccountPageUrl()
    {
        $selfLink = $this->getViewConfig()->getSelfLink();

        $seoEncoder = oxNew('oxseoencoder');

        return $seoEncoder->getStaticUrl($selfLink . 'cl=account');
    }

    /**
     * Returns translated string.
     *
     * @param string $string
     *
     * @return string
     */
    private function oeGdprBaseGetTranslatedString($string)
    {
        $languageId = oxRegistry::getLang()->getBaseLanguage();

        return oxRegistry::getLang()->translateString(
            $string,
            $languageId,
            false
        );
    }

    /**
     * Paginate ReviewAndRating list.
     *
     * @param array $reviewAndRatingList
     * @param int   $itemsCount
     * @param int   $offset
     *
     * @return array
     */
    private function oeGdprBaseGetPaginatedReviewAndRatingList(
        $reviewAndRatingList,
        $itemsCount,
        $offset
    )
    {
        return array_slice(
            $reviewAndRatingList,
            $offset,
            $itemsCount,
            true
        );
    }

    /**
     * @return oeGdprBaseContainer
     */
    private function oeGdprBaseGetContainer()
    {
        return oeGdprBaseContainer::getInstance();
    }
}
