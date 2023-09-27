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
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
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
import {useBlockProps} from '@wordpress/block-editor';
import {Button, FormFileUpload, RadioControl, SelectControl, TextareaControl, TextControl} from '@wordpress/components';
import {useEffect, useState} from "@wordpress/element";
import {useDispatch} from "@wordpress/data";


const VimeifyAPICore = window['WPVimeoVideos'] ? window['WPVimeoVideos'] : null;

const filterViewPrivacyOptions = (options) => {
    const newOptions = {};
    for (let i in options) {
        if (options[i].available) {
            newOptions[i] = options[i];
        }
    }
    return newOptions;
}

const Edit = ({attributes, setAttributes}) => {

    return (
        <>
            <div className="dgv-table-wrapper table-responsive ">
                <table className="dgv-table table">
                    <thead>
                    <tr>
                        <th className="dgv-head-title">Title</th>
                        <th className="dgv-head-date">Date</th>
                        <th className="dgv-head-actions">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td className="dgv-row-title">Meanwhile in Montenegro</td>
                        <td className="dgv-row-date">February 20, 2021</td>
                        <td className="dgv-row-actions">
                            <a href="http://vimeo.test/vimeo-upload/meanwhile-in-montenegro/" target="_blank" title="View">
                                <span className="vimeify-eye"></span>
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </>
    )
};
export default Edit;
