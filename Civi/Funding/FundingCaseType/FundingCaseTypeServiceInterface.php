<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseType;

/**
 * Marker for funding case type specific services. Implementations need to be
 * registered as tagged service in the DI container. The tag is the constant
 * SERVICE_TAG of the actual service interface or the FQCN of the actual service
 * interface if there's no such constant.
 *
 * The supported funding case types has to be specified in either of the
 * following ways:
 * - String for attribute "funding_case_type" in service tag.
 * - List of strings returned by public static method getSupportedFundingCaseTypes().
 * - String returned by public static method getSupportedFundingCaseType().
 * - List of strings in public class constant SUPPORTED_FUNDING_CASE_TYPES.
 * - String in public class constant SUPPORTED_FUNDING_CASE_TYPE.
 *
 * The funding case type "*" will be used as fallback.
 *
 * @see FallbackFundingCaseTypeServiceInterface
 */
interface FundingCaseTypeServiceInterface {}
