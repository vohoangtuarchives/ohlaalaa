<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Dfareporting;

class VideoFormatsListResponse extends \Google\Collection
{
  protected $collection_key = 'videoFormats';
  /**
   * @var string
   */
  public $kind;
  protected $videoFormatsType = VideoFormat::class;
  protected $videoFormatsDataType = 'array';
  public $videoFormats;

  /**
   * @param string
   */
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  /**
   * @return string
   */
  public function getKind()
  {
    return $this->kind;
  }
  /**
   * @param VideoFormat[]
   */
  public function setVideoFormats($videoFormats)
  {
    $this->videoFormats = $videoFormats;
  }
  /**
   * @return VideoFormat[]
   */
  public function getVideoFormats()
  {
    return $this->videoFormats;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(VideoFormatsListResponse::class, 'Google_Service_Dfareporting_VideoFormatsListResponse');
