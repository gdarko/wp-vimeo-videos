/********************************************************************
 * Copyright (C) 2023 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2023 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/wp-vimeo-videos/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
git  * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

import "./editor.scss"

import {__} from '@wordpress/i18n';
import {useBlockProps, BlockControls, InspectorControls} from '@wordpress/block-editor';
import {BaseControl, Button, FormFileUpload, FormToggle, RadioControl, SelectControl, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {useEffect, useState} from "@wordpress/element";

const Edit = ({attributes, setAttributes}) => {

    const blockProps = useBlockProps();

    const [authors, setAuthors] = useState(null);
    const [categories, setCategories] = useState(null);

    const createAuthorsOptions = (authors) => {
        const initial = [{label: __('Any', 'wp-vimeo-videos'), value: -1}]
        setAuthors(initial.concat(authors));
    }
    const createCategoriesOptions = (authors) => {
        const initial = [{label: __('Any', 'wp-vimeo-videos'), value: -1}]
        setCategories(initial.concat(authors));
    }

    if(authors === null) {
        wp.apiFetch({path: '/wp/v2/users'}).then(data => createAuthorsOptions(data.map((x) => {
            return {label: x.name, value: x.id}
        })));
    }
    if(categories === null) {
        wp.apiFetch({path: '/wp/v2/dgv-category'}).then(data => createCategoriesOptions(data.map((x) => {
            return {label: x.name, value: x.id}
        })));
    }

    return (
        <>
            <div {...blockProps}>
                {
                    <InspectorControls>
                        <div className="dgv-inspector-controls-block">
                            <fieldset>
                                <SelectControl
                                    label={__('Author', 'wp-vimeo-videos')}
                                    value={attributes.author}
                                    options={authors}
                                    onChange={ author => setAttributes( { author } ) }
                                />
                            </fieldset>
                            <fieldset>
                                <SelectControl
                                    label={__('Categories', 'wp-vimeo-videos')}
                                    value={attributes.category}
                                    options={categories}
                                    onChange={ category => setAttributes( { category } ) }
                                    multiple={true}
                                />
                            </fieldset>
                            <fieldset>
                                <SelectControl
                                    label={__('Order Direction', 'wp-vimeo-videos')}
                                    value={attributes.order}
                                    options={[
                                        {label: __('DESC', 'wp-vimeo-videos'), value: 'desc'},
                                        {label: __('ASC', 'wp-vimeo-videos'), value: 'asc'},
                                    ]}
                                    onChange={ order => setAttributes( { order } ) }
                                />
                            </fieldset>
                            <fieldset>
                                <SelectControl
                                    label={__('Order By', 'wp-vimeo-videos')}
                                    value={attributes.orderby}
                                    options={[
                                        {label: __('Title', 'wp-vimeo-videos'), value: 'title'},
                                        {label: __('Date', 'wp-vimeo-videos'), value: 'date'},
                                    ]}
                                    onChange={ orderby => setAttributes( { orderby } ) }
                                />
                            </fieldset>
                            <fieldset>
                                <TextControl
                                    label={__('Videos number', 'wp-vimeo-videos')}
                                    value={attributes.posts_per_page}
                                    onChange={ posts_per_page => setAttributes( { posts_per_page } ) }
                                    __nextHasNoMarginBottom
                                />
                            </fieldset>

                        </div>
                    </InspectorControls>
                }

                <div className="dgv-block-preview dgv-table-wrapper table-responsive ">
                    <table className="dgv-table table" border="0">
                        <thead>
                        <tr>
                            <th className="dgv-head-title">Title</th>
                            <th className="dgv-head-date">Date</th>
                            <th className="dgv-head-actions">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td className="dgv-row-title">Exaple vimeo video #1</td>
                            <td className="dgv-row-date">January 01, 2023</td>
                            <td className="dgv-row-actions">
                                <a href="#" target="_blank" title="View">
                                    <span className="vimeify-eye"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td className="dgv-row-title">Exaple vimeo video #2</td>
                            <td className="dgv-row-date">January 02, 2023</td>
                            <td className="dgv-row-actions">
                                <a href="#" target="_blank" title="View">
                                    <span className="vimeify-eye"></span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td className="dgv-row-title">Exaple vimeo video #3</td>
                            <td className="dgv-row-date">January 03, 2023</td>
                            <td className="dgv-row-actions">
                                <a href="#" target="_blank" title="View">
                                    <span className="vimeify-eye"></span>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    )
};
export default Edit;