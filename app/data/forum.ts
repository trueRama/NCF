/**
 * Every fact on this site lives here.
 *
 * Sourced from the *draft* 17th NCF concept note (22 July 2026) and from the
 * Doing Business Portal's own forum archive. Anything the concept note leaves
 * unnamed — a presenter given only by job title, a discussant with no
 * institution attached — carries `provisional: true` and is rendered with a
 * "to be confirmed" marker rather than being quietly presented as settled.
 */

export interface Speaker {
  name: string
  role: string
  org: string
  /** Which part of the programme they appear in. */
  billing: string
  /** True when the concept note names only the office, not the person. */
  provisional?: boolean
  /** Initials fall back to a monogram when there is no portrait. */
  photo?: string
}

export interface ProgrammeItem {
  text: string
  /** Nested list — discussants, remarks, sub-points. */
  children?: string[]
  label?: string
}

export interface ProgrammeBlock {
  id: string
  time: string
  kind: 'registration' | 'session' | 'break' | 'close'
  title: string
  /** Short strapline shown under the title in the session cards. */
  strapline?: string
  items?: ProgrammeItem[]
  chair?: { name: string; role: string }
}

export const EVENT = {
  edition: '17th',
  series: 'National Competitiveness Forum',
  shortName: 'NCF',
  title: '17th National Competitiveness Forum',
  theme:
    'From Funds to Firms: Turning GoU Wealth Creation Initiatives into Competitive Enterprises for Uganda’s US$500 Billion Economy',
  /** East Africa Time (UTC+3). Registration opens 8:00 am. */
  startsAt: '2026-09-25T08:00:00+03:00',
  dateLabel: 'Friday, 25 September 2026',
  timeLabel: '8:00 am – 5:00 pm EAT',
  venue: 'Kampala Serena Hotel',
  city: 'Kampala, Uganda',
  format: 'In person',
  convenedBy: 'Private Sector Development Unit',
  ministry: 'Ministry of Finance, Planning and Economic Development',
  directorate: 'Directorate of Economic Affairs',
  /** The concept note is a draft; say so rather than implying a final programme. */
  provisional: true,
  portalUrl: 'https://doingbusiness.fiika.dev',
} as const

/** Headline numbers behind the theme. Concept note, §Justification. */
export const HEADLINE_STATS = [
  { value: 'Shs 4.3tn', label: 'invested under the Parish Development Model', note: 'to June 2026' },
  { value: '10,589', label: 'parishes reached nationwide', note: 'PDM coverage' },
  { value: '3.7m+', label: 'households benefiting', note: 'by June 2026' },
  { value: 'US$18.04bn', label: 'exports of goods and services', note: '12 months to March 2026' },
] as const

export const INTRO = [
  'The 17th National Competitiveness Forum seeks to turn government-funded production into exports by linking small producers to firms with buyers, storage, financing and technical support.',
] as const

export const BACKGROUND = [
  'The Fourth National Development Plan (NDP IV, 2025/26–2029/30) and the Tenfold Growth Strategy set out Government’s vision to expand the economy from roughly USD 50 billion in FY 2022/23 to USD 500 billion by 2040, built on four high-multiplier anchors — agro-industrialisation, tourism, mineral-based development including oil and gas, and science, technology and innovation.',
  'From FY 2023/24 Government targets exports at 50% of GDP (from 12%), manufactured goods at 50% of merchandise exports (from 19%), medium and high-tech products at 50% of manufactures (from 21%), and a five-fold rise in agro-industrial export value from USD 4.0 billion to USD 20 billion a year — all by 2040.',
  'The Economic and Commercial Diplomacy Strategy has given Uganda’s missions abroad clear trade promotion targets. Demand created in those markets must now be fulfilled at the right standard, volume and speed — and that is where the wealth creation programmes come in.',
  'Government has rolled out the Parish Development Model, Emyooga, the Agriculture Credit Facility and the Youth Livelihood Programme, disbursing funds to households to monetise the economy through increased production of key commodities. The challenge is turning those beneficiaries into commercial, export-capable enterprises.',
] as const

/** The export delivery gap. Concept note, §Justification. */
export const CHALLENGES = [
  {
    icon: 'network',
    title: 'Fragmented supply',
    body: 'Coffee, dairy, grains, oilseeds, fisheries and horticulture are dominated by smallholders, micro-enterprises and SMEs operating in dispersed, uncoordinated units that cannot individually meet buyer requirements for volume, consistency, traceability and quality.',
  },
  {
    icon: 'trending-down',
    title: 'Low productivity, high losses',
    body: 'Yields per hectare and per worker remain well below regional and global benchmarks. Post-harvest losses in key agricultural value chains run at 20–40%, largely because processing technology is rudimentary.',
  },
  {
    icon: 'package',
    title: 'Limited value addition',
    body: 'Uganda still exports green coffee beans, raw hides, unprocessed minerals and primary produce — forfeiting the premium that grading, processing, packaging, branding and certification would earn.',
  },
  {
    icon: 'shield-alert',
    title: 'Weak compliance with market standards',
    body: 'Sanitary and phytosanitary rules, traceability and sustainability requirements in the EU, UK, Middle East and Asia lock out exporters who lack the institutional scale to invest in compliance systems.',
  },
] as const

export const PURPOSE =
  'The Forum aims to build consensus on practical interventions — including a mechanism that transforms Government’s wealth creation initiatives into drivers of monetisation and formalisation of export value chains.'

export const SOLUTION = {
  title: 'The proposed solution: an aggregation model',
  body: 'The concept note proposes an aggregation model with strong institutional linkages, using the value chain approach to build mutually beneficial relationships among farmers, traders, processors, cooperatives and other value chain actors — linking farmers to aggregators so that small, scattered production becomes export-ready volume.',
  requirements: [
    'A pre-determined market for the commodity',
    'Mass extension services and training for producers',
    'Compliance with the standards the buyer requires',
    'Post-harvest infrastructure at the point of aggregation',
    'Financing matched to the needs of farmers and aggregators',
  ],
}

export const OBJECTIVES = [
  'Assess what the wealth creation funds have actually built.',
  'Identify how to turn programme beneficiaries into businesses that can export.',
  'Strengthen the working relationship between Government, business, banks, development partners and universities.',
  'Recommend the policy and institutional reforms that would raise value addition.',
  'Align the funds with Uganda’s US$500 billion target.',
] as const

export const EXPECTED_OUTCOMES = [
  'Policy recommendations for strengthening the wealth creation programmes.',
  'A roadmap for moving from funding households to building firms.',
  'Commitments from stakeholders on financing, innovation and market access.',
  'A shortlist of priority value chains and the investment each one needs.',
  'A Forum communiqué setting out the resolutions and who delivers them.',
] as const

/** Annex 1 of the concept note, as a running order. */
export const PROGRAMME: ProgrammeBlock[] = [
  {
    id: 'registration',
    time: '8:00 – 8:30 am',
    kind: 'registration',
    title: 'Arrival and registration',
  },
  {
    id: 'opening',
    time: '8:30 – 10:00 am',
    kind: 'session',
    title: 'Opening session',
    strapline: 'Where the last Forum left us, and what Government has invested since.',
    chair: { name: 'Mr Moses Kaggwa', role: 'Ag. Director, Economic Affairs, MoFPED' },
    items: [
      { label: '15 mins', text: 'Welcoming remarks by the Head, Private Sector Development Unit — taking stock of the 16th NCF recommendations.' },
      { text: 'Keynote address by the Minister of Finance, Planning and Economic Development.' },
      { text: 'Uganda’s strategic investment into wealth creation initiatives to support the Tenfold Growth Strategy and NDP IV — Mr Ramathan Ggoobi, Permanent Secretary / Secretary to the Treasury.' },
      { text: 'Best practices on wealth creation funds and how they have promoted export growth — lessons from India.' },
    ],
  },
  {
    id: 'health-break',
    time: '10:00 – 10:30 am',
    kind: 'break',
    title: 'Health break',
  },
  {
    id: 'session-i',
    time: '10:30 am – 12:30 pm',
    kind: 'session',
    title: 'Session I — Agricultural export-oriented financing',
    strapline: 'Aligning available funding to support export development.',
    chair: { name: 'Mr Robert Wanok', role: 'Head, Retail Banking, Equity Bank' },
    items: [
      { text: 'Presentation by Stanbic Bank on closing the financing gap that keeps Ugandan firms out of regional and global markets — quality, standards, volumes.' },
      {
        label: '8 mins each',
        text: 'Discussants',
        children: ['Pearl Bank', 'Alliance for a Green Revolution in Africa (AGRA)', 'Pearl Capital Partners', 'aBi Trust', 'EMATA'],
      },
      { label: '20 mins', text: 'Open discussion' },
    ],
  },
  {
    id: 'lunch',
    time: '12:30 – 2:00 pm',
    kind: 'break',
    title: 'Lunch and networking',
  },
  {
    id: 'session-ii',
    time: '2:00 – 4:00 pm',
    kind: 'session',
    title: 'Session II — From funds’ beneficiaries to firms for exports',
    strapline: 'Using the value chain approach to turn small, scattered production into export-ready volumes.',
    chair: { name: 'Dr Edward Katende', role: 'Chief Executive Officer, Uganda Development Forum' },
    items: [
      { label: '20 mins', text: 'Paper on how scattered farmers can work through aggregators to produce for export — District Commercial Officer, Mubende District Local Government.' },
      {
        text: 'Discussants',
        children: [
          'MAAIF — Commissioner, Crop Production',
          'UNBS — Director, Standards',
          'National Agricultural Research Organisation (NARO)',
          'Asili',
          'Mukwano Group of Companies',
          'Rwahi Investments',
        ],
      },
      { label: '20 mins', text: 'Open discussion' },
    ],
  },
  {
    id: 'session-iii',
    time: '4:00 – 5:00 pm',
    kind: 'session',
    title: 'Session III — Key takeaways and what happens next',
    strapline: 'Implications for the FY 2027/28 budget, NDP IV and the Tenfold Growth Strategy.',
    chair: { name: 'Mr Zackey Kalega', role: 'Commissioner, Internal Trade, MTIC' },
    items: [
      { text: 'Key takeaways — Commissioner, Local Economic Growth Support (LEGS), Ministry of Local Government.' },
      {
        label: '5 mins each',
        text: 'Remarks',
        children: [
          'National Planning Authority',
          'Ag. Director, Economic Affairs — MoFPED',
          'Ag. Director, Budget — MoFPED',
          'Ag. Commissioner, Economic Development Policy and Research — MoFPED',
        ],
      },
      { label: '15 mins', text: 'Closing remarks by the Minister of State for Finance, Planning and Economic Development (Planning).' },
    ],
  },
  {
    id: 'departure',
    time: '5:00 pm',
    kind: 'close',
    title: 'Departure at leisure',
  },
]

/**
 * Speakers and chairs. `provisional: true` = the concept note gives the office
 * but not the person; the name shown is a placeholder pending confirmation.
 */
export const SPEAKERS: Speaker[] = [
  {
    name: 'Hon. Matia Kasaija',
    role: 'Minister of Finance, Planning and Economic Development',
    org: 'MoFPED',
    billing: 'Keynote address',
    provisional: true,
  },
  {
    name: 'Mr Ramathan Ggoobi',
    role: 'Permanent Secretary / Secretary to the Treasury',
    org: 'MoFPED',
    billing: 'Uganda’s strategic investment into wealth creation',
  },
  {
    name: 'Hon. Amos Lugolobi',
    role: 'Minister of State for Finance, Planning and Economic Development (Planning)',
    org: 'MoFPED',
    billing: 'Closing remarks',
    provisional: true,
  },
  {
    name: 'Ms Dianna Nanono',
    role: 'Head, Private Sector Development Unit',
    org: 'MoFPED',
    billing: 'Welcoming remarks — stock-take of the 16th NCF',
    provisional: true,
  },
  {
    name: 'Mr Moses Kaggwa',
    role: 'Ag. Director, Economic Affairs',
    org: 'MoFPED',
    billing: 'Chair — Opening session',
  },
  {
    name: 'Mr Robert Wanok',
    role: 'Head, Retail Banking',
    org: 'Equity Bank',
    billing: 'Chair — Session I, Export-oriented financing',
  },
  {
    name: 'Dr Edward Katende',
    role: 'Chief Executive Officer',
    org: 'Uganda Development Forum',
    billing: 'Chair — Session II, From funds to firms',
  },
  {
    name: 'Mr Zackey Kalega',
    role: 'Commissioner, Internal Trade',
    org: 'Ministry of Trade, Industry and Cooperatives',
    billing: 'Chair — Session III, Key takeaways',
  },
  {
    name: 'Ms Sarah Nabbanja',
    role: 'District Commercial Officer',
    org: 'Mubende District Local Government',
    billing: 'Lead paper — aggregation for export',
    provisional: true,
  },
  {
    name: 'Mr David Ssemwanga',
    role: 'Commissioner, Local Economic Growth Support (LEGS)',
    org: 'Ministry of Local Government',
    billing: 'Key takeaways',
    provisional: true,
  },
  {
    name: 'Mr Anil Sharma',
    role: 'Senior Adviser, Rural Enterprise Finance',
    org: 'Small Industries Development Bank of India',
    billing: 'Best practices from India',
    provisional: true,
  },
  {
    name: 'Ms Grace Muhumuza',
    role: 'Head, Agribusiness',
    org: 'Stanbic Bank Uganda',
    billing: 'Lead presentation — Session I',
    provisional: true,
  },
]

/** Institutions listed in the concept note as presenters or discussants. */
export const PARTICIPATING = {
  'Financial institutions and funds': ['Stanbic Bank', 'Equity Bank', 'Pearl Bank', 'Pearl Capital Partners', 'aBi Trust', 'EMATA', 'AGRA'],
  'Government and agencies': ['MoFPED', 'MAAIF', 'MTIC', 'Ministry of Local Government', 'UNBS', 'NARO', 'National Planning Authority', 'Mubende DLG'],
  'Private sector': ['Mukwano Group of Companies', 'Asili', 'Rwahi Investments', 'Uganda Development Forum'],
} as const

/** Annex II — what previous editions changed. */
export interface ArchiveEntry {
  year: number
  edition: string
  theme: string
  outcome: string
  photo?: string
}

export const ARCHIVE: ArchiveEntry[] = [
  {
    year: 2025,
    edition: '16th',
    theme: 'Leveraging regional opportunities to grow food processing and animal feeds sectors in Uganda',
    outcome:
      'An outcome matrix of 18 agreed actions across 12 issue areas naming 16 institutions, published January 2026. Recommendations adopted into the FY 2026/27 budget and NDP IV.',
    photo: '/img/forum/ncf16-opening-panel.jpg',
  },
  {
    year: 2024,
    edition: '15th',
    theme: 'The role of the public and private sector in realizing the knowledge economy agenda in Uganda',
    outcome: 'Recommendations on science, technology and innovation adopted by the FY 2025/26 budget and NDP IV.',
    photo: '/img/forum/ncf15-minister-address.jpg',
  },
  {
    year: 2023,
    edition: '14th',
    theme: 'Boosting private sector investments in agro-industry to take advantage of regional opportunities',
    outcome: 'Recommendations adopted by the FY 2024/25 budget and fed into the NDP IV development process.',
  },
  {
    year: 2022,
    edition: '13th',
    theme: 'Leveraging strategic export markets to boost investments for import substitution and export diversification',
    outcome:
      'Launched the second National Strategy for Private Sector Development (NSPSD II) with its sub-national interventions.',
  },
  {
    year: 2021,
    edition: '12th',
    theme: 'Business Unusual: the state of competitiveness and private sector performance in Uganda during Covid-19',
    outcome: 'Recommendations adopted by the FY 2022/23 budget; informed the design of NSPSD II.',
    photo: '/img/forum/ncf12-delegates-serena.jpg',
  },
  {
    year: 2020,
    edition: '11th',
    theme: 'Strengthening evidence-based private sector development interventions',
    outcome:
      'Informed the FY 2021/22 budget and the Covid-19 Economic Recovery Plan; pushed digitalisation, e-commerce and e-government as resilience measures.',
  },
  {
    year: 2019,
    edition: '10th',
    theme: 'Enhancing competitiveness in Uganda’s agro-industry',
    outcome:
      'Informed the National Business Development Services Framework, now coordinated by Enterprise Uganda, and the case for capitalising the Uganda Development Corporation.',
  },
  {
    year: 2018,
    edition: '9th',
    theme: 'Creating an enabling business environment for enterprises in Uganda',
    outcome: 'Informed the creation of the Makerere Innovation and Incubation Centre (MIIC).',
  },
  {
    year: 2017,
    edition: '8th',
    theme: 'Creating efficiencies for private sector development initiatives',
    outcome:
      'Informed the design of the first National Strategy for Private Sector Development, a five-year strategy supervised by the Private Sector Working Group.',
  },
]

/** What the 16th Forum agreed — the stock-take the opening session returns to. */
export const LAST_FORUM_PRIORITIES = [
  { title: 'PDM Pillar 3', body: 'Agro-industrialisation modalities under the Parish Development Model.' },
  { title: 'Human capital', body: 'Extension workers, animal nutritionists, feed processors, food technologists.' },
  { title: 'Value addition', body: 'Processing milling by-products, cassava and soya.' },
  { title: 'Standards', body: 'The Animal Feeds Act operationalised, EAC standards harmonised, UNBS certification cheaper.' },
  { title: 'Financing', body: 'INVITE, factoring facilities and targeted credit lines.' },
  { title: 'Regional markets', body: 'Non-tariff barriers addressed, export licensing digitised, duty-free access used.' },
  { title: 'Infrastructure', body: 'Bulking centres, storage, laboratories and reliable power.' },
  { title: 'Raw materials', body: 'Cassava, sunflower and insect protein in place of maize, soya and mukene.' },
  { title: 'Monitoring', body: 'Recommendations into the FY 2026/27 budget, tracked semi-annually.' },
] as const

/** Mirrors the `files.category` enum in the repository database. */
export type ResourceCategory = 'event_resources' | 'reports' | 'commodity_profiles'

export interface Resource {
  title: string
  description: string
  /** Which of the three repositories the Download and View buttons point at. */
  category: ResourceCategory
}

export const RESOURCES: Resource[] = [
  {
    title: 'Event Resources',
    category: 'event_resources',
    description:
      'The concept note, draft programme and delegate material for this Forum, including the justification, objectives and proposed aggregation model.',
  },
  {
    title: 'Reports',
    category: 'reports',
    description:
      'Outcome matrices and post-Forum reporting, including the 18 actions agreed at the 16th Forum with their lead institutions and current status.',
  },
  {
    title: 'Commodity Profiles',
    category: 'commodity_profiles',
    description:
      'Value-chain profiles for the priority commodities under discussion — production, processing, standards and market access for each.',
  },
]

export const GALLERY = [
  { src: '/img/forum/ncf16-opening-panel.jpg', caption: 'The opening panel of the 16th Forum.' },
  { src: '/img/forum/ncf16-keynote.jpg', caption: 'Keynote remarks on behalf of the Permanent Secretary / Secretary to the Treasury.' },
  { src: '/img/forum/ncf16-psdu-remarks.jpg', caption: 'The Private Sector Development Unit reports back on the previous Forum’s recommendations.' },
  { src: '/img/forum/ncf16-exhibition-stand.jpg', caption: 'A processed foods exhibitor at the Forum marketplace.' },
  { src: '/img/forum/ncf16-delegates-group.jpg', caption: 'Delegates at the close of the 16th Forum.' },
  { src: '/img/forum/ncf16-organising-team.jpg', caption: 'The Forum organising team.' },
]
